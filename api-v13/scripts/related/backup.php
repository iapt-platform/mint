<?php

/**
 * 备份生产 related_paragraphs：库内快照表 + 库外 csv 存档 + 清单。
 *
 * 用法：
 *   php scripts/related/backup.php                 # 表名自动带当天日期
 *   php scripts/related/backup.php --tag=20260909  # 指定后缀
 *   php scripts/related/backup.php --force         # 快照表已存在时覆盖
 *
 * 这是整个重建工程唯一允许写生产库的脚本，而且只做 CREATE TABLE AS，不改原表。
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);

$source = 'related_paragraphs';
$tag = $options['tag'] ?? date('Ymd');
$snapshot = rp_assert_table("related_paragraphs_bak_$tag");
$log = new RpLogger('backup', rp_report_dir());

$log->info("源表：$source");
$log->info("快照表：$snapshot");

$sourceCount = DB::table($source)->count();
$log->info("源表行数：$sourceCount");
if ($sourceCount === 0) {
    $log->error('源表为空，拒绝备份——这几乎一定是连错库了。');
    $log->finish();
    exit(1);
}

$exists = DB::selectOne('select to_regclass(?) as reg', [$snapshot])->reg !== null;
if ($exists) {
    if (! isset($options['force'])) {
        $existing = DB::table($snapshot)->count();
        $log->error("快照表已存在（$existing 行）。要覆盖请加 --force。");
        $log->finish();
        exit(1);
    }
    $log->warn('快照表已存在，--force 生效，先 DROP。');
    DB::statement("drop table $snapshot");
}

$log->info('建快照表中…');
DB::statement("create table $snapshot as select * from $source");
$snapshotCount = DB::table($snapshot)->count();
$log->info("快照表行数：$snapshotCount");

if ($snapshotCount !== $sourceCount) {
    $log->error("行数不一致：源 $sourceCount vs 快照 $snapshotCount");
    $log->finish();
    exit(1);
}

// 快照表只用于比对，建最小索引即可（按 book/para 定位、按 book_name+cs_para 配对）。
$log->info('建索引中…');
DB::statement("create index on $snapshot (book, para)");
DB::statement("create index on $snapshot (book_name, cs_para)");

// 库外存档：库没了也能拿回数据。
$archiveDir = rp_data_dir().'/backup';
if (! is_dir($archiveDir)) {
    mkdir($archiveDir, 0o755, true);
}
$csvPath = "$archiveDir/$snapshot.csv";
$log->info("导出 csv：$csvPath");

$handle = fopen($csvPath, 'w');
fputcsv($handle, ['book', 'para', 'book_id', 'cs_para', 'book_name']);
$rows = 0;
DB::table($source)->orderBy('book')->orderBy('para')->orderBy('cs_para')->orderBy('id')
    ->select('book', 'para', 'book_id', 'cs_para', 'book_name')
    ->chunk(20000, function ($chunk) use ($handle, &$rows, $log) {
        foreach ($chunk as $row) {
            fputcsv($handle, [$row->book, $row->para, $row->book_id, $row->cs_para, $row->book_name]);
            $rows++;
        }
        if ($rows % 200000 === 0) {
            $log->info("  已导出 $rows 行");
        }
    });
fclose($handle);
$log->info("csv 行数：$rows");

$manifest = [
    'created_at' => date('c'),
    'database' => DB::connection()->getDatabaseName(),
    'source_table' => $source,
    'snapshot_table' => $snapshot,
    'rows' => $sourceCount,
    'csv_path' => $csvPath,
    'csv_sha256' => hash_file('sha256', $csvPath),
    'csv_bytes' => filesize($csvPath),
];
rp_json_write("$archiveDir/$snapshot.manifest.json", $manifest);
$log->info("清单：$archiveDir/$snapshot.manifest.json");
$log->info('sha256: '.$manifest['csv_sha256']);

$log->finish();
