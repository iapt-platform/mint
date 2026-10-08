<?php

/**
 * 生成书级配置表 book_config.json：每本 VRI 文件的类型（mūla/义注/复注）与
 * 书名分段（哪一段段落属于哪个 book_name）。
 *
 * 为什么需要：123 本文件的 wbw 标记里完全没有书名信息（只有裸 paraN），书名要从
 * 别处来。证据按可信度排序：
 *   1. wbw 的 book_tag 标记（dn1@3、an3@323）——文件自带的书界，最硬
 *   2. wbw 的 paraN_xxx 后缀——逐段自带书名
 *   3. 旧表的主导书名——最后兜底。旧表的错在 cs 值不在书名上，唯一例外是
 *      kn10_A/kn10_B，而那两本恰好有 book_tag 证据，会被 1 覆盖掉。
 *
 * overrides 区是人工修正区，脚本不动它，每次重跑都保留。
 *
 * 用法：php scripts/related/build_book_config.php [--baseline=related_paragraphs_bak_20260909]
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$baseline = rp_assert_table($options['baseline'] ?? 'related_paragraphs_bak_20260909');
$markers = $options['markers'] ?? 'rp_markers';
$configPath = __DIR__.'/book_config.json';

$log = new RpLogger('build_book_config', rp_report_dir());
$log->info("书名兜底基线：$baseline");

// 已有的人工修正区必须原样带过来，否则每次重跑都把人的判断冲掉。
$overrides = new stdClass;
if (file_exists($configPath)) {
    $existing = rp_json_read($configPath)['overrides'] ?? [];
    $overrides = $existing === [] ? new stdClass : (object) $existing;
    $log->info('保留已有 overrides：'.count((array) $overrides).' 条');
}

// ── 文件类型：按 level-1 段落的 tag 判定，粒度是 book_id 不是 VRI 文件 ──────
// book 207 一个文件里既有 pāḷi 又有 ṭīkā，按文件判会判错。
$typeRows = DB::select("
    select p.book, p.paragraph, bt.sn as book_id, string_agg(t.name, ',') as tags
    from pali_texts p
    join tag_maps m on m.anchor_id = p.uid and m.table_name = 'pali_texts'
    join tags t on t.id = m.tag_id
    left join book_titles bt on bt.book = p.book and bt.paragraph = p.paragraph
    where p.level = 1
    group by p.book, p.paragraph, bt.sn
    order by p.book, p.paragraph
");

$fileTypes = [];
foreach ($typeRows as $row) {
    $tags = explode(',', $row->tags);
    $type = match (true) {
        (bool) array_intersect($tags, ['ṭīkā', 'mūlaṭīkā', 'anuṭīkā', 'abhinavaṭīkā', 'purāṇaṭīkā']) => 'tika',
        in_array('aṭṭhakathā', $tags, true) => 'atthakatha',
        (bool) array_intersect($tags, ['mūla', 'pāḷi']) => 'mula',
        default => 'unknown',
    };
    $fileTypes[$row->book][] = [
        'book_id' => $row->book_id,
        'from_paragraph' => $row->paragraph,
        'type' => $type,
        'tags' => $tags,
    ];
    $log->count("file_type.$type");
    if ($type === 'unknown') {
        $log->unresolved('file_type_unknown', [
            'book' => $row->book, 'paragraph' => $row->paragraph, 'tags' => $row->tags,
        ]);
    }
}

// ── 书名证据 ─────────────────────────────────────────────────────────────
$books = DB::select('select distinct book from pali_texts order by book');
$config = [];

foreach ($books as $b) {
    $book = $b->book;
    $maxPara = (int) DB::table('pali_texts')->where('book', $book)->max('paragraph');

    // 证据 1：book_tag 标记，给出文件内的书界切换点
    $tagMarks = DB::select(
        "select paragraph, book_name from $markers where book = ? and kind = 'book_tag' order by paragraph",
        [$book]
    );

    // 证据 2：paraN_xxx 后缀，逐段自带书名
    $suffixMarks = DB::select(
        "select paragraph, book_name from $markers
         where book = ? and kind in ('single','interval') and book_name is not null
         order by paragraph, wid",
        [$book]
    );

    // 证据 3：旧表逐段落的名字，按连续段压缩。
    // 空书名也必须成段——book 177 一个文件里装了四部书，只有头一部是 abhi8，
    // 后三部（nāmarūpaparicchedo 等）本来就没有 SC 书名。早先过滤掉空名，
    // 结果 abhi8 越过书界铺满全文件，把 10695 行错配进了 abhi8 的注释链。
    $oldNames = DB::select(
        "select para, max(book_name) as book_name from $baseline where book = ?
         group by para order by para",
        [$book]
    );

    $source = match (true) {
        $tagMarks !== [] => 'wbw_book_tag',
        $suffixMarks !== [] => 'wbw_suffix',
        $oldNames !== [] => 'old_table',
        default => 'none',
    };

    $points = [];   // paragraph => book_name 的切换点
    if ($source === 'wbw_book_tag') {
        foreach ($tagMarks as $m) {
            $points[(int) $m->paragraph] = $m->book_name;
        }
        // book_tag 只给书界，段内若还有后缀且与之矛盾，记下来复核
        foreach ($suffixMarks as $m) {
            $expected = null;
            foreach ($points as $p => $n) {
                if ($p <= $m->paragraph) {
                    $expected = $n;
                }
            }
            if ($expected !== null && $expected !== $m->book_name) {
                $log->count('name_conflict.tag_vs_suffix');
                $log->unresolved('name_conflict_tag_vs_suffix', [
                    'book' => $book, 'paragraph' => (int) $m->paragraph,
                    'book_tag_says' => $expected, 'suffix_says' => $m->book_name,
                ]);
            }
        }
    } elseif ($source === 'wbw_suffix') {
        $prev = null;
        foreach ($suffixMarks as $m) {
            if ($m->book_name !== $prev) {
                $points[(int) $m->paragraph] = $m->book_name;
                $prev = $m->book_name;
            }
        }
    } elseif ($source === 'old_table') {
        $prev = null;
        foreach ($oldNames as $m) {
            // 序言名 dn1_A 是义注开头的正常设计，保留；其余同名连续段合并
            if ($m->book_name !== $prev) {
                $points[(int) $m->para] = $m->book_name;
                $prev = $m->book_name;
            }
        }
    }

    // 义注/复注开头的序言段用 {book}_A 命名（dn1_A 型，全库 12 本）。wbw 里带
    // {mula}_0 章标记的书（99/103/130/133）导出器能自己认出来，但另有 6 本
    // （181/185/188/192 及 179/211 的文件中段）wbw 完全没有痕迹，序言范围只存在于
    // 旧表里。这里把旧表的 _A 连续段叠加进切换点，免得序言被并进正文名下。
    if ($source !== 'old_table') {
        // 注意：序言段里出现 paraN_mula 标记是正常的，不能拿首个 cs 标记当序言终点。
        // book 130 的序言（mn1_0@5）里就有 para1_mn1@40，序言真正的终点是章标记
        // mn1_1@47；book 192 同样。序言段照旧表原样叠加即可。
        $runName = null;
        $runFrom = null;
        $flushRun = function (int $endPara) use (&$points, &$runName, &$runFrom, $log, $book, $oldNames) {
            if ($runName === null) {
                return;
            }
            $base = preg_replace('/_A$/', '', $runName);

            // 真序言的后面一定跟着正文（基名）。book 143 的 kn10_A 铺满整个文件、
            // 后面根本没有 kn10——那是 E3 的错名而不是序言，绝不能叠加进来。
            $baseFollows = false;
            foreach ($oldNames as $row) {
                if ((int) $row->para > $endPara && $row->book_name === $base) {
                    $baseFollows = true;
                    break;
                }
            }
            if (! $baseFollows) {
                $log->count('prologue_overlay.rejected');
                $log->info("book $book 拒绝叠加 $runName@$runFrom-{$endPara}：其后无 $base 正文，判为错名而非序言");
                $runName = null;
                $runFrom = null;

                return;
            }
            // 序言区间内的旧切换点要清掉：book_tag 的 dn1@3 落在 dn1_A@1-198 中间，
            // 不清就会把序言从 para 3 截断，只剩两段。
            $base = $points[$endPara + 1] ?? $base;
            foreach (array_keys($points) as $existing) {
                if ($existing > $runFrom && $existing <= $endPara) {
                    unset($points[$existing]);
                }
            }
            $points[$runFrom] = $runName;
            if (! isset($points[$endPara + 1])) {
                $points[$endPara + 1] = $base;
            }
            $log->count('prologue_overlay');
            $log->info("book $book 叠加序言段 $runName@$runFrom-$endPara");
            $runName = null;
            $runFrom = null;
        };
        $lastPara = null;
        foreach ($oldNames as $m) {
            $para = (int) $m->para;
            $isPrologue = str_ends_with((string) $m->book_name, '_A');
            if ($isPrologue && $m->book_name === $runName && $para === $lastPara + 1) {
                $lastPara = $para;

                continue;
            }
            if ($runName !== null) {
                $flushRun($lastPara);
            }
            if ($isPrologue) {
                $runName = $m->book_name;
                $runFrom = $para;
            }
            $lastPara = $para;
        }
        if ($runName !== null) {
            $flushRun($lastPara);
        }
        ksort($points);
    }

    // 切换点 → 区间
    $segments = [];
    $keys = array_keys($points);
    foreach ($keys as $i => $from) {
        $segments[] = [
            'para_from' => $from,
            'para_to' => isset($keys[$i + 1]) ? $keys[$i + 1] - 1 : $maxPara,
            'book_name' => $points[$from],
        ];
    }

    $config[(string) $book] = [
        'max_paragraph' => $maxPara,
        'name_source' => $source,
        'file_types' => $fileTypes[$book] ?? [],
        'segments' => $segments,
    ];
    $log->count("name_source.$source");
    if ($source === 'none') {
        $log->unresolved('book_name_unknown', ['book' => $book]);
    }
    if (count($segments) > 1) {
        $log->info(sprintf('book %-3d %-14s %d 段：%s', $book, $source, count($segments),
            implode(' ', array_map(fn ($s) => "{$s['book_name']}@{$s['para_from']}", $segments))));
    }
}

rp_json_write($configPath, [
    'generated_at' => date('c'),
    'baseline_table' => $baseline,
    'purpose' => '每本 VRI 文件的类型与书名分段。导出器优先用 wbw 现场证据，此表只在文件本身没有书名信息时兜底。overrides 是人工修正区，重跑不覆盖。',
    'books' => $config,
    'overrides' => $overrides,
]);
$log->info("配置表：{$configPath}（".count($config).' 本）');

$log->finish();
