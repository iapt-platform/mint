<?php

/**
 * 把 wbw_templates 里的 .ctl. 锚点解析成工作表 rp_markers。
 *
 * 导出器和检测器都要按标记做集合运算，逐次在 PHP 里重解析 33 万行既慢又容易两边
 * 规则跑偏，所以物化一次、共用一份。改了 MarkerParser 就要重跑这个脚本。
 *
 * 用法：php scripts/related/build_markers.php [--table=rp_markers]
 */

require __DIR__.'/lib/boot.php';
require __DIR__.'/lib/MarkerParser.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$table = $options['table'] ?? 'rp_markers';
if (! preg_match('/^rp_[a-z0-9_]+$/', $table)) {
    throw new InvalidArgumentException("工作表名必须以 rp_ 开头：$table");
}

$log = new RpLogger('build_markers', rp_report_dir());
$log->info("目标工作表：$table");

DB::statement("drop table if exists $table");
DB::statement("
    create table $table (
        book        integer not null,
        paragraph   integer not null,
        wid         integer not null,
        word        varchar(1024) not null,
        kind        varchar(16) not null,
        book_name   varchar(64),
        cs_from     integer,
        cs_to       integer,
        cs_count    integer not null default 0,
        numbers     varchar(64),
        is_abbrev   boolean not null default false,
        repaired    text,
        error       text
    )
");

$total = DB::table('wbw_templates')->where('type', '.ctl.')->where('style', '#a#')->count();
$log->info("待解析锚点：$total 行");

$buffer = [];
$written = 0;

$flush = function () use (&$buffer, &$written, $table, $log) {
    if ($buffer === []) {
        return;
    }
    DB::table($table)->insert($buffer);
    $written += count($buffer);
    $buffer = [];
    if ($written % 50000 === 0) {
        $log->info("  已写入 $written 行");
    }
};

DB::table('wbw_templates')
    ->where('type', '.ctl.')
    ->where('style', '#a#')
    ->select('book', 'paragraph', 'wid', 'word')
    ->orderBy('book')->orderBy('paragraph')->orderBy('wid')
    ->chunk(20000, function ($chunk) use (&$buffer, $flush, $log) {
        foreach ($chunk as $row) {
            $parsed = MarkerParser::parse($row->word);
            $log->count('kind.'.$parsed['kind']);

            // 缩写区间：右端位数少于左端，正是旧生成器翻车的那一类，单独打标以便统计。
            $isAbbrev = false;
            if ($parsed['kind'] === MarkerParser::KIND_INTERVAL
                && preg_match('/^para([0-9]+)-([0-9]+)/', $row->word, $m)
                && strlen($m[2]) < strlen($m[1])) {
                $isAbbrev = true;
                $log->count('interval.abbrev');
            }

            if ($parsed['error']) {
                $log->unresolved('marker_parse_failed', [
                    'book' => $row->book, 'paragraph' => $row->paragraph,
                    'word' => $row->word, 'detail' => $parsed['error'],
                ]);
            }
            if ($parsed['repaired']) {
                $log->warn("修补标记 book={$row->book} para={$row->paragraph}: {$parsed['repaired']}");
                $log->unresolved('marker_repaired', [
                    'book' => $row->book, 'paragraph' => $row->paragraph,
                    'word' => $row->word, 'detail' => $parsed['repaired'],
                ]);
            }

            $buffer[] = [
                'book' => $row->book,
                'paragraph' => $row->paragraph,
                'wid' => $row->wid,
                'word' => $row->word,
                'kind' => $parsed['kind'],
                'book_name' => $parsed['book_name'],
                'cs_from' => $parsed['cs'] === [] ? null : $parsed['cs'][0],
                'cs_to' => $parsed['cs'] === [] ? null : end($parsed['cs']),
                'cs_count' => count($parsed['cs']),
                'numbers' => $parsed['numbers'] ? implode('.', $parsed['numbers']) : null,
                'is_abbrev' => $isAbbrev,
                'repaired' => $parsed['repaired'],
                'error' => $parsed['error'],
            ];
            if (count($buffer) >= 5000) {
                $flush();
            }
        }
    });
$flush();

$log->info("写入完成：$written 行");
if ($written !== $total) {
    $log->error("行数不符：源 $total vs 写入 $written");
}

$log->info('建索引中…');
DB::statement("create index on $table (book, paragraph)");
DB::statement("create index on $table (kind)");
DB::statement("create index on $table (book_name)");
DB::statement("create index on $table (is_abbrev) where is_abbrev");

$log->finish();
