<?php

/**
 * 从 wbw_templates 的锚点标记重放 related_paragraphs 第一版。
 *
 * 原则是「查不到优于查到错」：任何一处判不定就留空、记 issue，绝不沿用上一个 cs 值。
 * 旧生成链路（VRI html → cs6_para.csv → laravel import）翻车的地方全在这条原则上：
 * 换书后无标记就把上一个 cs 一路铺下去（E8），缩写区间不认识就跳过或写成六位废值
 * （E1/E7），倒序区间 begin>end 让导入的 for 循环空转（book 103 para 1464 的成因）。
 *
 * 本版不处理章回绕后缀（E2），那要看标题层级，留给下一批；先把确定性规则跑干净，
 * 混在一起会看不出是哪条规则错了。
 *
 * 用法：
 *   php scripts/related/export.php --to=related_paragraphs_v1
 *   php scripts/related/export.php --to=related_paragraphs_v1 --book=136,98,207   # 样例验货
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$target = rp_assert_table($options['to'] ?? 'related_paragraphs_v1');
$markersTable = $options['markers'] ?? 'rp_markers';
$onlyBooks = isset($options['book']) ? array_map('intval', explode(',', $options['book'])) : null;

$log = new RpLogger('export', rp_report_dir());
$log->info("目标表：$target");
if ($onlyBooks) {
    $log->info('只导出：'.implode(',', $onlyBooks));
}

$config = rp_json_read(__DIR__.'/book_config.json');
$bookConfig = $config['books'];
$overrides = $config['overrides'] ?? [];

// ── 建表 ─────────────────────────────────────────────────────────────────
if (! $onlyBooks) {
    DB::statement("drop table if exists $target");
}
DB::statement("
    create table if not exists $target (
        id bigserial primary key,
        book integer not null,
        para integer not null,
        book_id integer not null,
        cs_para integer not null,
        book_name varchar(64) not null,
        created_at timestamp(0), updated_at timestamp(0)
    )
");
if ($onlyBooks) {
    DB::table($target)->whereIn('book', $onlyBooks)->delete();
}

// ── 预载 ─────────────────────────────────────────────────────────────────
$log->info('预载 book_titles…');
$titles = [];
foreach (DB::select('select book, paragraph, sn from book_titles order by book, paragraph') as $t) {
    $titles[$t->book][] = ['paragraph' => (int) $t->paragraph, 'sn' => (int) $t->sn];
}

$books = array_map(
    fn ($r) => (int) $r->book,
    DB::select('select distinct book from pali_texts order by book')
);
if ($onlyBooks) {
    $books = array_values(array_intersect($books, $onlyBooks));
}
$log->info('待导出文件数：'.count($books));

$buffer = [];
$written = 0;
$now = date('Y-m-d H:i:s');

$flush = function () use (&$buffer, &$written, $target) {
    if ($buffer === []) {
        return;
    }
    foreach (array_chunk($buffer, 5000) as $chunk) {
        DB::table($target)->insert($chunk);
    }
    $written += count($buffer);
    $buffer = [];
};

/** 在配置分段里查某段落该用什么书名；段落早于首段时沿用首段的名。 */
$configName = function (int $book, int $para) use ($bookConfig, $overrides): ?string {
    // overrides 是人工判定区，优先级高于自动推导出来的分段。
    $segments = $overrides[(string) $book]['segments']
        ?? $bookConfig[(string) $book]['segments']
        ?? [];
    if ($segments === []) {
        return null;
    }
    $name = $segments[0]['book_name'];
    foreach ($segments as $s) {
        if ($para >= $s['para_from']) {
            $name = $s['book_name'];
        }
    }

    return $name;
};

foreach ($books as $book) {
    $bookTitles = $titles[$book] ?? [];

    // 段落全集取 pali_texts 而不是 wbw_templates：pali_texts 是严格超集
    // （523284 段 vs 516113 段），wbw 少的那 7171 段散在 21 本里，
    // 按 wbw 取全集会白丢这些段落的行。wbw 只负责提供标记。
    $paragraphs = array_map(
        fn ($r) => (int) $r->paragraph,
        DB::select('select distinct paragraph from pali_texts where book = ? order by paragraph', [$book])
    );
    if ($paragraphs === []) {
        continue;
    }

    $markerRows = DB::select(
        "select paragraph, wid, word, kind, book_name, cs_from, cs_to, numbers, error
         from $markersTable where book = ? and kind <> 'page' order by paragraph, wid",
        [$book]
    );
    $byParagraph = [];
    foreach ($markerRows as $m) {
        $byParagraph[(int) $m->paragraph][] = $m;
    }

    // ── book_id 分段：book_titles 的每个条目开一段，之前的段落是书前页 ────────
    $segments = [];
    if ($bookTitles === []) {
        $segments[] = ['sn' => 0, 'from' => 1, 'to' => PHP_INT_MAX];
    } else {
        if ($bookTitles[0]['paragraph'] > 1) {
            $segments[] = ['sn' => 0, 'from' => 1, 'to' => $bookTitles[0]['paragraph'] - 1];
        }
        foreach ($bookTitles as $i => $t) {
            $segments[] = [
                'sn' => $t['sn'],
                'from' => $t['paragraph'],
                'to' => $bookTitles[$i + 1]['paragraph'] ?? PHP_INT_MAX,
            ];
            if (isset($bookTitles[$i + 1])) {
                $segments[count($segments) - 1]['to'] = $bookTitles[$i + 1]['paragraph'] - 1;
            }
        }
    }

    $bookName = null;
    $prologue = false;

    // 义注文件的开头几段（书前页）在 {mula}_0 序言标记之前，但内容上属于序言。
    // 文件里首个章标记是 _0 时，起手就用 {mula}_A，免得前几段挂到正文名下。
    foreach ($markerRows as $m) {
        if ($m->kind === 'chapter') {
            if ($m->numbers === '0') {
                $bookName = $m->book_name.'_A';
                $prologue = true;
                $log->count('event.prologue_seeded');
            }
            break;
        }
    }
    // 文件自身没有任何书名标记时，书名只能全程由配置表分段决定。
    // 配置表在所有书里都当书名基线：它已经把 book_tag 书界、人工 override 和
    // 序言段（dn1_A 型）合并成一份完整分段。标记仍然可以就地覆盖——paraN_xxx 后缀
    // 是最贴身的证据，book_tag 是书界事件，两者都在下面的标记循环里生效。
    $nameFromConfig = true;

    foreach ($segments as $seg) {
        $segParagraphs = array_values(array_filter(
            $paragraphs,
            fn ($p) => $p >= $seg['from'] && $p <= $seg['to']
        ));
        if ($segParagraphs === []) {
            continue;
        }

        // ⭐ 防污染核心规则：整段没有任何 cs 标记，就不许给它任何非零 cs。
        // 旧生成器在这里把换书前的 cs 一路铺下去，book 207 的 1499 行恒 cs=75 就是这么来的。
        // 但也不能整段不输出：这些段落的行多半是 cs=0 的占位行（E4 的无注释体系书、
        // 义注序言、书前页），下游 ParaInfoController 按段落取行，删掉就是白丢覆盖。
        // 输出 cs=0 即可——index() 取锚点时要求 cs_para > 0，cs=0 行不会产生任何配对，
        // 既保住覆盖又不编造关系。仍然记 issue，标出待 LLM 补齐的位置。
        $segMarkerCount = 0;
        foreach ($segParagraphs as $p) {
            foreach ($byParagraph[$p] ?? [] as $m) {
                if (in_array($m->kind, ['single', 'interval'], true)) {
                    $segMarkerCount++;
                }
            }
        }
        if ($segMarkerCount === 0 && $seg['sn'] !== 0) {
            $log->count('segment_without_markers');
            $log->unresolved('segment_without_markers', [
                'book' => $book, 'book_id' => $seg['sn'],
                'para_from' => $segParagraphs[0], 'para_to' => end($segParagraphs),
                'paragraphs' => count($segParagraphs),
                'detail' => '整段无 cs 标记，全部输出 cs=0 占位，待补（E8/E9）',
            ]);
        }

        // 段起点：cs 归零等待首个标记。
        // 书名分两种来源：文件自带 book_tag/后缀标记的，交给标记驱动；没有任何现场
        // 证据的（123 本，如 book 98 一个文件里装了 abhi3..abhi7），只能逐段落查配置表
        // ——这类文件的书界在 wbw 里没有任何痕迹。
        $csList = [0];
        $bookName ??= $configName($book, $segParagraphs[0]);

        foreach ($segParagraphs as $para) {
            $skipParagraph = false;

            $configNameHere = $configName($book, $para);
            if ($nameFromConfig) {
                $bookName = $configNameHere;
            }

            foreach ($byParagraph[$para] ?? [] as $m) {
                switch ($m->kind) {
                    case 'book_tag':
                        // 文件内的书界切换（an2@1 an3@323…），新书从 cs=0 起算
                        if ($bookName !== $m->book_name) {
                            $log->count('event.book_switch');
                        }
                        $bookName = $m->book_name;
                        $prologue = false;
                        $csList = [0];
                        break;

                    case 'chapter':
                        $numbers = $m->numbers === null ? [] : array_map('intval', explode('.', $m->numbers));
                        if ($numbers === [0]) {
                            // {mula}_0 = 义注开头的序言（Ganthārambhakathā），不对应原文段落
                            $prologue = true;
                            $bookName = $m->book_name.'_A';
                            $csList = [0];
                            $log->count('event.prologue_begin');
                        } else {
                            if ($prologue) {
                                $prologue = false;
                                $bookName = $m->book_name;
                                $csList = [0];
                                $log->count('event.prologue_end');
                            }
                            $log->count('event.chapter');
                        }
                        break;

                    case 'single':
                    case 'interval':
                        // 序言段里的 paraN_mula 后缀不夺名：序言归 {mula}_A，
                        // 后缀只说明它在注释哪一段，不表示它已经进入正文
                        // （book 181 的 an1_A@1-138 内就有 para1_an1 起的一串）。
                        if ($m->book_name !== null && $configNameHere !== $m->book_name.'_A') {
                            $bookName = $m->book_name;
                        }
                        // cs 标记不结束序言：序言里带 paraN_mula 是正常体例
                        // （book 130 的 mn1_0 序言内就有 para1_mn1），序言只由
                        // {mula}_{N>0} 章标记结束。
                        $csList = range((int) $m->cs_from, (int) $m->cs_to);
                        $log->count($m->kind === 'interval' ? 'marker.interval' : 'marker.single');
                        break;

                    case 'truncated':
                        // para179- 型：区间右端缺失，本段留空，上下文不变
                        $skipParagraph = true;
                        $log->unresolved('truncated_marker', [
                            'book' => $book, 'paragraph' => $para, 'word' => $m->word,
                            'detail' => $m->error ?? '区间右端缺失',
                        ]);
                        break;

                    default:
                        $log->count('marker.ignored.'.$m->kind);
                }
            }

            if ($skipParagraph) {
                continue;
            }

            if ($bookName === null) {
                // 没有任何书名证据：E4 的无注释体系书走到这里是正常的，输出空名占位
                $bookName = '';
            }

            foreach ($csList as $cs) {
                $buffer[] = [
                    'book' => $book, 'para' => $para, 'book_id' => $seg['sn'],
                    'cs_para' => $cs, 'book_name' => $bookName,
                    'created_at' => $now, 'updated_at' => $now,
                ];
            }
            $log->count('rows.emitted', count($csList));
        }
    }

    $flush();
    $log->info(sprintf('book %-3d 完成，累计 %d 行', $book, $written));
}

$flush();
$log->info("写入完成：$written 行");

if (! $onlyBooks) {
    $log->info('建索引中…');
    DB::statement("create index on $target (book)");
    DB::statement("create index on $target (para)");
    DB::statement("create index on $target (book_name)");
    DB::statement("create index on $target (book_name, cs_para)");
    DB::statement("create index on $target (book, para, cs_para)");
}

$log->finish();
