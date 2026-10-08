<?php

/**
 * 章回绕的跨侧对齐：给每个 book_name 的每个「cs 重置段」定一个章号，两侧用同一套章号。
 *
 * 为什么不能各干各的：book 143（mūla kn10）有 42 个章标记，book 107（义注 kn10）一个
 * 都没有。单方面按标记加后缀，会让 kn10_1 只出现在 mūla 侧、义注侧还叫 kn10，
 * 两侧的配对全部打断——比不加后缀更糟。所以必须先对齐，再一起加。
 *
 * 对齐靠什么：注释侧的 cs 值就是被注书的段落号，所以两侧每一章的 max(cs) 应当接近
 * （实测 kn10 前 11 章：663/657、140/134、188/184、113/108…，注释侧略低，因为它不注
 * 每章最后几段）。但段数常对不上（kn10 mūla 42 段 vs 义注 34 段，中间还夹着 max=2 的
 * 碎段），所以按序号硬配会漂，得做带空位的序列比对。
 *
 * 用法：
 *   php scripts/related/align_chapters.php --table=related_paragraphs_v1
 *   php scripts/related/align_chapters.php --table=related_paragraphs_v1 --name=kn10 --verbose
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$table = rp_assert_table($options['table'] ?? 'related_paragraphs_v1');
$onlyName = $options['name'] ?? null;
$verbose = isset($options['verbose']);
/** 对齐得分低于此值的段判为不可信，不加后缀，转人工/LLM。 */
$minConfidence = (float) ($options['min-confidence'] ?? 0.6);

$log = new RpLogger('align_chapters', rp_report_dir());
$log->info("被对齐表：{$table}");
$log->info("置信度阈值：{$minConfidence}");

$config = rp_json_read(__DIR__.'/book_config.json');

/** 某 VRI 文件在注释链条中的位置。同一文件可能多种，取首个 level-1 的判定。 */
$sideOf = function (int $book) use ($config): string {
    $types = $config['books'][(string) $book]['file_types'] ?? [];

    return $types[0]['type'] ?? 'unknown';
};

// ── 找出所有有回绕的 book_name ────────────────────────────────────────────
$names = DB::select("
    with s as (
        select book_name, book, para, cs_para,
               lag(cs_para) over (partition by book, book_name order by para, cs_para) as prev
        from {$table} where cs_para > 0 and book_name <> ''
    )
    select book_name, count(*) as wraps
    from s where cs_para = 1 and prev > 1
    group by book_name order by count(*) desc
");
if ($onlyName) {
    $names = array_values(array_filter($names, fn ($n) => $n->book_name === $onlyName));
}
$log->info('有回绕的 book_name：'.count($names).' 个');

/**
 * 把一个 (book_name, book) 的行切成「cs 重置段」。
 *
 * @return list<array{seg:int, book:int, para_from:int, para_to:int, cs_min:int, cs_max:int, rows:int}>
 */
$segmentsOf = function (string $name, int $book) use ($table): array {
    $rows = DB::select("
        select para, min(cs_para) as cs_min, max(cs_para) as cs_max
        from {$table} where book_name = ? and book = ? and cs_para > 0
        group by para order by para
    ", [$name, $book]);

    $segments = [];
    $current = null;
    $prevMin = null;
    foreach ($rows as $r) {
        // 按段落的 cs_min 判重置。用 cs_max 会被区间展开骗到：连续的 para1-2 段落
        // 每段 cs 都是 {1,2}，看 max 就成了 …2→1 的连环「重置」。
        $isReset = $prevMin !== null && (int) $r->cs_min === 1 && $prevMin > 1;
        if ($current === null || $isReset) {
            if ($current !== null) {
                $segments[] = $current;
            }
            $current = [
                'seg' => count($segments), 'book' => $book,
                'para_from' => (int) $r->para, 'para_to' => (int) $r->para,
                'cs_min' => (int) $r->cs_min, 'cs_max' => (int) $r->cs_max, 'rows' => 0,
            ];
        }
        $current['para_to'] = (int) $r->para;
        $current['cs_max'] = max($current['cs_max'], (int) $r->cs_max);
        $current['cs_min'] = min($current['cs_min'], (int) $r->cs_min);
        $current['rows']++;
        $prevMin = (int) $r->cs_min;
    }
    if ($current !== null) {
        $segments[] = $current;
    }

    return $segments;
};

/** 两段 max(cs) 的接近程度，值域约 [-2, 2]。注释侧本就略低于 mūla，故不对称惩罚。 */
$score = function (int $refMax, int $queryMax): float {
    $scale = max($refMax, $queryMax, 1);
    $diff = abs($refMax - $queryMax) / $scale;
    $s = 2.0 - 4.0 * $diff;
    if ($queryMax > $refMax) {
        $s -= 0.5;   // 注释侧超过被注书的章长，不合体例，压一压
    }

    return $s;
};

/**
 * 带空位的序列比对（Needleman–Wunsch）。
 * 返回 query 段下标 → ref 段下标；对不上的段返回 null，由调用方并入前一章。
 *
 * @return array<int, ?int>
 */
$align = function (array $refMax, array $queryMax) use ($score): array {
    $m = count($refMax);
    $n = count($queryMax);
    $gapRef = -0.6;      // 跳过一章参考段：该章没有注释，常见
    $gapQuery = -1.2;    // 跳过一个查询段：多半是碎段，代价高些

    $dp = array_fill(0, $m + 1, array_fill(0, $n + 1, 0.0));
    $from = array_fill(0, $m + 1, array_fill(0, $n + 1, ''));
    for ($i = 1; $i <= $m; $i++) {
        $dp[$i][0] = $dp[$i - 1][0] + $gapRef;
        $from[$i][0] = 'up';
    }
    for ($j = 1; $j <= $n; $j++) {
        $dp[0][$j] = $dp[0][$j - 1] + $gapQuery;
        $from[0][$j] = 'left';
    }
    for ($i = 1; $i <= $m; $i++) {
        for ($j = 1; $j <= $n; $j++) {
            $diag = $dp[$i - 1][$j - 1] + $score($refMax[$i - 1], $queryMax[$j - 1]);
            $up = $dp[$i - 1][$j] + $gapRef;
            $left = $dp[$i][$j - 1] + $gapQuery;
            $best = max($diag, $up, $left);
            $dp[$i][$j] = $best;
            $from[$i][$j] = $best === $diag ? 'diag' : ($best === $up ? 'up' : 'left');
        }
    }

    $map = [];
    $i = $m;
    $j = $n;
    while ($i > 0 || $j > 0) {
        $dir = $from[$i][$j];
        if ($dir === 'diag') {
            $map[$j - 1] = $i - 1;
            $i--;
            $j--;
        } elseif ($dir === 'up') {
            $i--;
        } else {
            $map[$j - 1] = null;
            $j--;
        }
    }
    ksort($map);

    return $map;
};

$result = [];
foreach ($names as $nameRow) {
    $name = $nameRow->book_name;
    $books = array_map(
        fn ($r) => (int) $r->book,
        DB::select("select distinct book from {$table} where book_name = ? order by book", [$name])
    );

    // 按侧分组，mūla 侧作参考；没有 mūla 侧就用行数最多的那一侧
    $bySide = [];
    foreach ($books as $book) {
        $bySide[$sideOf($book)][] = $book;
    }
    $refSide = isset($bySide['mula']) ? 'mula' : array_key_first($bySide);
    if ($refSide === null) {
        $log->unresolved('chapter_align_no_side', ['book_name' => $name]);

        continue;
    }

    // 参考侧的段序列（跨文件按 book 顺序拼接）即章号定义
    $refSegments = [];
    foreach ($bySide[$refSide] as $book) {
        foreach ($segmentsOf($name, $book) as $s) {
            $refSegments[] = $s;
        }
    }
    if ($refSegments === []) {
        continue;
    }
    $refMax = array_column($refSegments, 'cs_max');

    $entry = [
        'book_name' => $name,
        'wraps' => (int) $nameRow->wraps,
        'ref_side' => $refSide,
        'ref_books' => $bySide[$refSide],
        'chapters' => count($refSegments),
        'assignments' => [],
        'unaligned' => 0,
        'confidence' => 1.0,
    ];

    $alignedTotal = 0;
    $segTotal = 0;

    foreach ($bySide as $side => $sideBooks) {
        foreach ($sideBooks as $book) {
            $segments = $segmentsOf($name, $book);
            if ($segments === []) {
                continue;
            }

            if ($side === $refSide) {
                // 参考侧：段号即章号，但要按跨文件拼接后的全局下标
                $offset = 0;
                foreach ($bySide[$refSide] as $b) {
                    if ($b === $book) {
                        break;
                    }
                    $offset += count($segmentsOf($name, $b));
                }
                $map = [];
                foreach (array_keys($segments) as $k) {
                    $map[$k] = $offset + $k;
                }
            } else {
                $map = $align($refMax, array_column($segments, 'cs_max'));
            }

            $lastChapter = null;
            foreach ($segments as $k => $seg) {
                $chapter = $map[$k] ?? null;
                $segTotal++;
                if ($chapter === null) {
                    // 对不上的碎段并入前一章：它多半是注释侧的一次多余重置
                    $chapter = $lastChapter;
                    $entry['unaligned']++;
                    $log->count('segment_merged_into_previous');
                } else {
                    $alignedTotal++;
                    $lastChapter = $chapter;
                }
                if ($chapter === null) {
                    $log->unresolved('chapter_align_orphan_segment', [
                        'book_name' => $name, 'book' => $book,
                        'para_from' => $seg['para_from'], 'para_to' => $seg['para_to'],
                        'cs_max' => $seg['cs_max'],
                    ]);

                    continue;
                }
                $entry['assignments'][] = [
                    'book' => $book, 'side' => $side,
                    'para_from' => $seg['para_from'], 'para_to' => $seg['para_to'],
                    'cs_max' => $seg['cs_max'],
                    'chapter' => $chapter + 1,
                    'ref_cs_max' => $refSegments[$chapter]['cs_max'],
                ];
            }
        }
    }

    $entry['confidence'] = $segTotal > 0 ? round($alignedTotal / $segTotal, 3) : 0.0;
    $entry['applicable'] = $entry['confidence'] >= $minConfidence && $entry['chapters'] > 1;
    $result[$name] = $entry;

    $log->info(sprintf('%-8s 章数 %-3d 段数 %-4d 对上 %-4d 置信 %.3f %s',
        $name, $entry['chapters'], $segTotal, $alignedTotal, $entry['confidence'],
        $entry['applicable'] ? '' : '← 置信不足，不加后缀'));

    if ($verbose) {
        foreach ($entry['assignments'] as $a) {
            $log->info(sprintf('    book %-3d %-10s para %-6d-%-6d cs_max %-5d → 章 %-3d (参考 %d)',
                $a['book'], $a['side'], $a['para_from'], $a['para_to'], $a['cs_max'],
                $a['chapter'], $a['ref_cs_max']));
        }
    }
}

$applicable = array_filter($result, fn ($e) => $e['applicable']);
$log->info(sprintf('可加后缀的 book_name：%d / %d', count($applicable), count($result)));

$path = rp_report_dir().'/chapter_alignment.json';
rp_json_write($path, [
    'generated_at' => date('c'),
    'table' => $table,
    'min_confidence' => $minConfidence,
    'names' => $result,
]);
$log->info("对齐表：{$path}");

$log->finish();
