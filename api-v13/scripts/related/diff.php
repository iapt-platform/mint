<?php

/**
 * 两张 related 表的逐段落差异报告。
 *
 * 迭代的收敛判据是「每一条 diff 都能归因」——要么是修好了，要么是已知留空，不允许
 * 有说不清来源的差异。所以这里不只给总数，还按类型分桶并各留样本，供逐轮核对。
 *
 * 用法：
 *   php scripts/related/diff.php --old=related_paragraphs_bak_20260909 --new=related_paragraphs_v1
 *   php scripts/related/diff.php --new=related_paragraphs_v1 --book=98 --samples=40
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$old = rp_assert_table($options['old'] ?? 'related_paragraphs_bak_20260909');
$new = rp_assert_table($options['new'] ?? 'related_paragraphs_v1');
$sampleLimit = (int) ($options['samples'] ?? 15);
$onlyBooks = isset($options['book']) ? array_map('intval', explode(',', $options['book'])) : null;

$log = new RpLogger('diff', rp_report_dir());
$log->info("旧：$old");
$log->info("新：$new");

$bookFilter = $onlyBooks ? 'and book in ('.implode(',', $onlyBooks).')' : '';
$log->info('比对范围：'.($onlyBooks ? implode(',', $onlyBooks) : '全库'));

// 每个 (book, para) 压成一行「书名:cs 列表」的签名，逐段落比对。
$sql = "
    with o as (
        select book, para,
               string_agg(distinct book_name || ':' || cs_para, ',' order by book_name || ':' || cs_para) as sig,
               count(*) as rows
        from $old where true $bookFilter group by book, para
    ),
    n as (
        select book, para,
               string_agg(distinct book_name || ':' || cs_para, ',' order by book_name || ':' || cs_para) as sig,
               count(*) as rows
        from $new where true $bookFilter group by book, para
    )
    select
        coalesce(o.book, n.book) as book,
        coalesce(o.para, n.para) as para,
        o.sig as old_sig, n.sig as new_sig,
        coalesce(o.rows, 0) as old_rows, coalesce(n.rows, 0) as new_rows,
        case
            when o.book is null then 'only_in_new'
            when n.book is null then 'only_in_old'
            when o.sig = n.sig then 'same'
            else 'changed'
        end as verdict
    from o full outer join n on n.book = o.book and n.para = o.para
";

$log->info('比对中…');
$buckets = [];
$samples = [];
$perBook = [];
$total = 0;

foreach (DB::cursor($sql) as $row) {
    $total++;
    $verdict = $row->verdict;

    if ($verdict === 'changed') {
        // 细分：只是 cs 变了，还是书名也变了
        $oldNames = [];
        $newNames = [];
        foreach (explode(',', (string) $row->old_sig) as $pair) {
            $oldNames[substr($pair, 0, strrpos($pair, ':'))] = true;
        }
        foreach (explode(',', (string) $row->new_sig) as $pair) {
            $newNames[substr($pair, 0, strrpos($pair, ':'))] = true;
        }
        $verdict = array_keys($oldNames) === array_keys($newNames) ? 'cs_changed' : 'name_changed';
    }

    $buckets[$verdict] = ($buckets[$verdict] ?? 0) + 1;
    if ($verdict !== 'same') {
        $perBook[$row->book][$verdict] = ($perBook[$row->book][$verdict] ?? 0) + 1;
        if (count($samples[$verdict] ?? []) < $sampleLimit) {
            $samples[$verdict][] = [
                'book' => (int) $row->book, 'para' => (int) $row->para,
                'old' => $row->old_sig, 'new' => $row->new_sig,
            ];
        }
    }
}

ksort($buckets);
$log->info('---- 差异分桶（按段落计） ----');
foreach ($buckets as $verdict => $n) {
    $log->info(sprintf('  %-14s %8d  %5.1f%%', $verdict, $n, $total ? $n / $total * 100 : 0));
}

foreach ($samples as $verdict => $items) {
    $log->info("---- $verdict 样本 ----");
    foreach ($items as $s) {
        $log->info(sprintf('  book %-3d para %-6d 旧[%s] → 新[%s]',
            $s['book'], $s['para'], $s['old'] ?? '—', $s['new'] ?? '—'));
    }
}

// 差异最集中的书优先看
$ranked = [];
foreach ($perBook as $book => $b) {
    $ranked[] = ['book' => (int) $book, 'total' => array_sum($b)] + $b;
}
usort($ranked, fn ($a, $b) => $b['total'] <=> $a['total']);

$log->info('---- 差异最多的 20 本 ----');
foreach (array_slice($ranked, 0, 20) as $r) {
    $log->info(sprintf('  book %-3d 共 %-6d  %s', $r['book'], $r['total'],
        json_encode(array_diff_key($r, ['book' => 1, 'total' => 1]), JSON_UNESCAPED_UNICODE)));
}

$path = rp_report_dir()."/diff.$new.json";
rp_json_write($path, [
    'generated_at' => date('c'),
    'old_table' => $old, 'new_table' => $new,
    'scope' => $onlyBooks ?? 'all',
    'paragraphs_compared' => $total,
    'buckets' => $buckets,
    'per_book' => $ranked,
    'samples' => $samples,
]);
$log->info("报告：$path");

$log->finish();
