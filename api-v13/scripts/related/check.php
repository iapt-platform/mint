<?php

/**
 * related_paragraphs 记分卡：按 known_issues.json 的探测器给任意一张 related 表打分。
 *
 * 这是整个重建工程的标尺——每一轮「导出 → 检测 → 交给模型评估 → 改规则」都靠它
 * 回答两个问题：错误变少了没有？本来正确的书有没有被搞坏？
 *
 * 用法：
 *   php scripts/related/check.php --table=related_paragraphs_bak_20260909 --save-baseline
 *   php scripts/related/check.php --table=related_paragraphs_v1
 *
 * 依赖 rp_markers（先跑 build_markers.php）。
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$table = rp_assert_table($options['table'] ?? 'related_paragraphs');
$markers = $options['markers'] ?? 'rp_markers';
$saveBaseline = isset($options['save-baseline']);

$known = rp_json_read(__DIR__.'/known_issues.json');
$params = $known['params'];
$log = new RpLogger('check.'.$table, rp_report_dir());
$log->info("被检表：$table");
$log->info("标记表：$markers");

$one = fn (string $sql, array $bind = []) => (array) DB::selectOne($sql, $bind);
$all = fn (string $sql, array $bind = []) => array_map(fn ($r) => (array) $r, DB::select($sql, $bind));

$results = [];

/** 记一条探测结果，并与 known_issues.json 的基线对照。 */
$record = function (string $id, array $measured, ?string $note = null) use (&$results, $known, $log) {
    $spec = null;
    foreach ($known['detectors'] as $d) {
        if ($d['id'] === $id) {
            $spec = $d;
            break;
        }
    }
    $results[$id] = [
        'title' => $spec['title'] ?? $id,
        'severity' => $spec['severity'] ?? 'unknown',
        'baseline' => $spec['baseline'] ?? null,
        'target_v1' => $spec['target_v1'] ?? null,
        'measured' => $measured,
        'note' => $note,
    ];
    $log->info(sprintf('[%-24s] %s', $id, json_encode($measured, JSON_UNESCAPED_UNICODE)));
    if ($note) {
        $log->info('  '.$note);
    }
};

// ── E1 cs 乱码值 ──────────────────────────────────────────────────────────
$threshold = $params['cs_garbage_threshold'];
$record('E1_cs_garbage', $one(
    "select count(*)::int as rows, count(distinct book)::int as books from $table where cs_para > ?",
    [$threshold]
));

// ── E2 章回绕：同 (book, book_name) 内 cs 从大值回到 1 ────────────────────
$record('E2_chapter_wrap', $one("
    with s as (
        select book, book_name, cs_para,
               lag(cs_para) over (partition by book, book_name order by para, cs_para) as prev
        from $table where cs_para > 0 and book_name <> ''
    )
    select count(*)::int as wrap_points, count(distinct book)::int as books
    from s where cs_para = 1 and prev > 1
"));

// ── E3 kn10 家族 ─────────────────────────────────────────────────────────
$record('E3_kn10_missing', $one("
    select
        count(*) filter (where book_name = 'kn10')::int   as kn10_rows,
        count(*) filter (where book_name = 'kn10_A')::int as kn10_a_rows,
        count(*) filter (where book_name = 'kn10_B')::int as kn10_b_rows
    from $table
"));

// ── E4 空 book_name（预期保留） ───────────────────────────────────────────
$record('E4_empty_book_name', $one(
    "select count(*)::int as rows, count(distinct book)::int as books from $table where book_name = ''"
));

// ── E5 book_id 未赋值 ────────────────────────────────────────────────────
$record('E5_book_id_zero', $one(
    "select count(*)::int as rows, count(distinct book)::int as books from $table where book_id = 0"
));

// ── E6 覆盖缺口：wbw 有这个段落，related 没有任何行 ────────────────────────
$record('E6_coverage_gap', $one("
    with wbw as (select distinct book, paragraph from pali_texts),
         rel as (select distinct book, para from $table)
    select count(*)::int as segments, count(distinct w.book)::int as books
    from wbw w left join rel r on r.book = w.book and r.para = w.paragraph
    where r.book is null
"));

// ── E7 缩写区间：旧生成器在这里丢段或产废值 ────────────────────────────────
$record('E7_abbrev_interval_lost', $one("
    with seg as (select distinct book, paragraph from $markers where is_abbrev),
         hit as (
            select s.book, s.paragraph,
                   count(r.id)::int as rows,
                   count(r.id) filter (where r.cs_para > $threshold)::int as garbage
            from seg s left join $table r on r.book = s.book and r.para = s.paragraph
            group by 1, 2
         )
    select
        count(*)::int as marker_segments,
        count(distinct book)::int as books,
        count(*) filter (where rows = 0)::int as segments_without_rows,
        count(*) filter (where rows > 0 and garbage > 0)::int as segments_with_garbage
    from hit
"));

// ── E8 书边界污染平铺 ─────────────────────────────────────────────────────
// 污染的准确签名是「整段一个 cs 标记都没有，却被写进了非零 cs」——那个值只能是
// 从上一本沿用下来的。早先按「cs 值种类少、行数多」判会把章回绕误判成污染
// （book 152 有 516 个标记却只 24 个 cs 值，那是 22 次回绕），按标记判才分得开。
// cs 恒为 0 的整段不算污染：0 是「未赋值」，不是编造出来的值。
$flat = $all("
    with seg as (
        select bt.book, bt.sn as book_id, bt.paragraph as p_from,
               coalesce(lead(bt.paragraph) over (partition by bt.book order by bt.paragraph) - 1, 2147483647) as p_to
        from book_titles bt
    ),
    book_marks as (
        select book, count(*) as n from $markers where kind in ('single','interval') group by 1
    )
    select s.book, s.book_id, s.p_from, x.rows, x.max_cs, x.vals
    from seg s
    join book_marks bm on bm.book = s.book
    cross join lateral (
        select count(*)::int as rows, coalesce(max(cs_para), 0)::int as max_cs,
               count(distinct cs_para)::int as vals
        from $table r where r.book = s.book and r.para between s.p_from and s.p_to
    ) x
    where x.rows > 0 and x.max_cs > 0
      and not exists (
        select 1 from $markers m
        where m.book = s.book and m.paragraph between s.p_from and s.p_to
          and m.kind in ('single','interval')
      )
    order by x.rows desc
");
$record('E8_flat_pollution', [
    'groups' => count($flat),
    'rows' => array_sum(array_column($flat, 'rows')),
], $flat === [] ? null : '污染段：'.json_encode($flat, JSON_UNESCAPED_UNICODE));

// ── E9 全书无标记：related 有行但 wbw 无 para 标记 ─────────────────────────
$noMarker = $all("
    with rel as (select book, count(*)::int as rows from $table group by 1),
         mk  as (select book, count(*)::int as markers from $markers
                 where kind in ('single', 'interval') group by 1)
    select rel.book, rel.rows from rel left join mk on mk.book = rel.book
    where coalesce(mk.markers, 0) = 0 order by rel.book
");
$record('E9_no_marker_books', [
    'books' => count($noMarker),
    'book_list' => array_column($noMarker, 'book'),
], $noMarker === [] ? null : '各本行数：'.json_encode($noMarker, JSON_UNESCAPED_UNICODE));

// ── E10 自造名（预期保留） ────────────────────────────────────────────────
$record('E10_selfmade_names', $one("
    select
        count(*) filter (where book_name = 'NK')::int   as nk_rows,
        count(*) filter (where book_name like 'subo%')::int as subo_rows,
        count(*) filter (where book_name = 'dict')::int as dict_rows
    from $table
"));

// ── D 连续性：同 (book, book_name, cs_para) 的 para 应构成连续区间 ──────────
$record('D_continuity', $one("
    with g as (
        select book, book_name, cs_para, para,
               para - row_number() over (partition by book, book_name, cs_para order by para) as grp
        from $table where cs_para > 0 and book_name <> ''
    ), seg as (
        select book, book_name, cs_para, count(distinct grp)::int as segments
        from g group by 1, 2, 3
    )
    select count(*)::int as broken_groups, count(distinct book)::int as books,
           coalesce(sum(segments - 1), 0)::int as extra_segments
    from seg where segments > 1
"));

// ── D 镜像对齐：注释链条两侧同名书的 cs 上界应一致 ─────────────────────────
$watch = ['dn1', 'kn6', 'kn9', 'abhi5', 'abhi3', 'abhi4', 'abhi6', 'abhi7'];
$mirror = [];
foreach ($watch as $name) {
    // 必须排除乱码值：旧表里 abhi4/5/6/7 的 max 全是六位废值（44022 等），
    // 不滤掉就看不出真实的跨侧对齐情况。
    $perFile = $all(
        "select book, max(cs_para)::int as max_cs, count(*)::int as rows
         from $table where book_name = ? and cs_para <= ? group by 1 order by 1",
        [$name, $threshold]
    );
    $mirror[$name] = [
        'overall_max' => $perFile === [] ? null : max(array_column($perFile, 'max_cs')),
        'per_file' => $perFile,
    ];
}
$aligned = $known['detectors'];
$expectedMax = [];
foreach ($aligned as $d) {
    if ($d['id'] === 'D_mirror_alignment') {
        $expectedMax = $d['baseline']['aligned'];
    }
}
$deviations = [];
foreach ($expectedMax as $name => $expected) {
    if (($mirror[$name]['overall_max'] ?? null) !== $expected) {
        $deviations[$name] = ['expected' => $expected, 'actual' => $mirror[$name]['overall_max'] ?? null];
    }
}
$record('D_mirror_alignment', [
    'deviations' => count($deviations),
    'detail' => $deviations,
], '各名 max(cs_para)：'.json_encode(
    array_map(fn ($m) => $m['overall_max'], $mirror),
    JSON_UNESCAPED_UNICODE
));
$results['D_mirror_alignment']['per_file'] = $mirror;

// ── D 区间展开：区间标记的展开行数必须等于 cs_to − cs_from + 1 ──────────────
$record('D_interval_expansion', $one("
    with mk as (
        select book, paragraph, min(cs_from)::int as cs_from, max(cs_to)::int as cs_to,
               max(cs_to) - min(cs_from) + 1 as expect
        from $markers where kind = 'interval' group by 1, 2
    ), got as (
        select mk.book, mk.paragraph, mk.expect,
               count(distinct r.cs_para)::int as actual
        from mk left join $table r on r.book = mk.book and r.para = mk.paragraph
             and r.cs_para between mk.cs_from and mk.cs_to
        group by 1, 2, 3
    )
    select count(*)::int as interval_markers,
           count(*) filter (where actual <> expect)::int as mismatched_markers,
           count(*) filter (where actual = 0)::int as empty_markers
    from got
"));

// ── 逐本记分卡：把 book 级错误信号汇总，零信号的书即基线冻结对象 ─────────────
$log->info('生成逐本记分卡…');
$scorecard = $all("
    with rel as (
        select book,
               count(*)::int as rows,
               count(distinct book_name)::int as names,
               count(*) filter (where cs_para > $threshold)::int as garbage_rows,
               count(*) filter (where book_id = 0)::int as no_book_id_rows,
               count(*) filter (where book_name = '')::int as empty_name_rows
        from $table group by 1
    ),
    wrap as (
        select book, count(*)::int as wrap_points from (
            select book, book_name, cs_para,
                   lag(cs_para) over (partition by book, book_name order by para, cs_para) as prev
            from $table where cs_para > 0 and book_name <> ''
        ) s where cs_para = 1 and prev > 1 group by 1
    ),
    abbrev as (
        select s.book, count(*) filter (where h.rows = 0)::int as abbrev_lost from (
            select distinct book, paragraph from $markers where is_abbrev
        ) s join lateral (
            select count(r.id)::int as rows from $table r where r.book = s.book and r.para = s.paragraph
        ) h on true group by 1
    ),
    gap as (
        select w.book, count(*)::int as gap_segments
        from (select distinct book, paragraph from wbw_templates) w
        left join (select distinct book, para from $table) r on r.book = w.book and r.para = w.paragraph
        where r.book is null group by 1
    )
    select rel.book, rel.rows, rel.names, rel.garbage_rows, rel.no_book_id_rows, rel.empty_name_rows,
           coalesce(wrap.wrap_points, 0) as wrap_points,
           coalesce(abbrev.abbrev_lost, 0) as abbrev_lost,
           coalesce(gap.gap_segments, 0) as gap_segments
    from rel
    left join wrap on wrap.book = rel.book
    left join abbrev on abbrev.book = rel.book
    left join gap on gap.book = rel.book
    order by rel.book
");

$flatBooks = [];
foreach ($flat as $f) {
    $flatBooks[$f['book']] = ($flatBooks[$f['book']] ?? 0) + 1;
}
$clean = [];
$dirty = [];
foreach ($scorecard as &$row) {
    $row['flat_groups'] = $flatBooks[$row['book']] ?? 0;
    // 空 book_name 是 E4 的预期状态，不算错误信号；其余四类算。
    $row['signals'] = $row['garbage_rows'] + $row['wrap_points']
        + $row['abbrev_lost'] + $row['flat_groups'] + $row['gap_segments'];
    if ($row['signals'] === 0) {
        $clean[] = $row['book'];
    } else {
        $dirty[] = $row['book'];
    }
}
unset($row);

$log->info(sprintf('逐本记分：合格 %d 本，有信号 %d 本，共 %d 本',
    count($clean), count($dirty), count($scorecard)));

$summary = [
    'checked_at' => date('c'),
    'database' => DB::connection()->getDatabaseName(),
    'table' => $table,
    'markers_table' => $markers,
    'total_rows' => DB::table($table)->count(),
    'detectors' => $results,
    'books' => ['clean' => $clean, 'dirty' => $dirty, 'scorecard' => $scorecard],
];

$reportPath = rp_report_dir()."/check.$table.json";
rp_json_write($reportPath, $summary);
$log->info("报告：$reportPath");

if ($saveBaseline) {
    $baselinePath = __DIR__.'/baseline.json';
    rp_json_write($baselinePath, [
        'frozen_at' => date('c'),
        'source_table' => $table,
        'database' => DB::connection()->getDatabaseName(),
        'purpose' => '旧表的实测基线。clean_books 是零错误信号的书，重建后这些书一本都不许掉出来；detectors 是各探测器的旧表读数，用于回答「错误是否减少」。',
        'clean_books' => $clean,
        'detectors' => array_map(fn ($r) => $r['measured'], $results),
        'scorecard' => $scorecard,
    ]);
    $log->info("基线已冻结：$baselinePath");
}

$log->finish();
