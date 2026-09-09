<?php

/**
 * 按 align_chapters.php 的对齐结果给 book_name 加章后缀，产出下一版表。
 *
 * 加后缀是为了让 (book_name, cs_para) 这个配对键重新变得唯一：回绕使同一个 key 在
 * 几十个章里重复，index() 一次查询就把几十章的段落全捞回来。后缀必须两侧同时加、
 * 用同一套章号，只给一侧加会把配对整个打断——所以只处理 align 判定为可信的 book_name，
 * 其余原样留给补丁/LLM。
 *
 * 光靠 align 的置信度不够。实测 vin8 的 books 200/201 都覆盖 cs 1..3183（是并列的两侧，
 * 不是上下册），却被当成顺序拼接标成了「第 1 章 / 第 2 章」，该名的跨文件配对 100% 报废；
 * dict 的 books 9↔21 同理。侧别判定要做到永不出错是不现实的，所以每个 book_name 改名
 * 前后都实测一次跨文件配对段数，掉了就整名回滚——指标说了算，不是规则说了算。
 *
 * 用法：
 *   php scripts/related/apply_chapters.php --from=related_paragraphs_v1 --to=related_paragraphs_v2
 */

require __DIR__.'/lib/boot.php';

use Illuminate\Support\Facades\DB;

[$options] = rp_args($argv);
$from = rp_assert_table($options['from'] ?? 'related_paragraphs_v1');
$to = rp_assert_table($options['to'] ?? 'related_paragraphs_v2');

$log = new RpLogger('apply_chapters', rp_report_dir());
$log->info("源表：{$from}");
$log->info("目标表：{$to}");

$alignment = rp_json_read(rp_report_dir().'/chapter_alignment.json');
if ($alignment['table'] !== $from) {
    $log->error("对齐表是针对 {$alignment['table']} 算的，与源表 {$from} 不符，先重跑 align_chapters.php");
    $log->finish();
    exit(1);
}

$log->info('复制源表…');
DB::statement("drop table if exists {$to}");
DB::statement("create table {$to} as select * from {$from}");
DB::statement("create index on {$to} (book, para)");
$log->info('行数：'.DB::table($to)->count());

$applied = 0;
$rowsChanged = 0;
$reverted = 0;

/**
 * 某个 book_name（含其所有章后缀变体）下，有多少 (book, para) 通过**可信的**配对键
 * 跨文件命中。可信 = 该键在任何单本书里命中的段落数不超过 $maxParas。
 *
 * 为什么要排除膨胀键：回绕让 kn10:1 这样的键在 56 个章里重复，一次查询捞回上千段，
 * 那不是配对是噪声。直接数「配对段数」会把这些噪声算成资产——实测 kn10 加后缀后该数
 * 从 23757 掉到 6279，看着像灾难，其实掉的几乎全是 book 144 章 43-56 的虚假配对
 * （那几章本来就没有义注，是靠撞在一起的 kn10:1 假装配上的）。只数非膨胀键，
 * 加对了后缀这个数会涨（膨胀键裂成一批干净键），加错了才会掉。
 */
$pairedParagraphs = function (string $table, string $baseName, int $maxParas = 10): int {
    $pattern = str_replace(['_', '%'], ['\\_', '\\%'], $baseName);

    return (int) DB::selectOne("
        with mine as (
            select book, para, book_name, cs_para
            from {$table}
            where cs_para > 0 and (book_name = ? or book_name like ?)
        ),
        per_book as (
            select book_name, cs_para, book, count(distinct para) as paras
            from mine group by book_name, cs_para, book
        ),
        k as (
            select book_name, cs_para from per_book
            group by book_name, cs_para
            having count(*) >= 2 and max(paras) <= ?
        )
        select count(distinct (m.book, m.para))::int as n
        from mine m join k on k.book_name = m.book_name and k.cs_para = m.cs_para
    ", [$baseName, $pattern.'\_%', $maxParas])->n;
};

foreach ($alignment['names'] as $name => $entry) {
    if (! $entry['applicable']) {
        $log->count('name.skipped_low_confidence');
        $log->unresolved('chapter_align_low_confidence', [
            'book_name' => $name, 'confidence' => $entry['confidence'],
            'chapters' => $entry['chapters'], 'wraps' => $entry['wraps'],
            'detail' => '跨侧对齐置信不足，未加后缀，待补丁或 LLM 判定',
        ]);

        continue;
    }
    if (str_ends_with($name, '_A')) {
        // 序言名不分章：它本就不对应正文段落，加后缀没有意义
        $log->count('name.skipped_prologue');

        continue;
    }

    $before = $pairedParagraphs($to, $name);

    $changed = 0;
    foreach ($entry['assignments'] as $a) {
        $changed += DB::table($to)
            ->where('book', $a['book'])
            ->where('book_name', $name)
            ->whereBetween('para', [$a['para_from'], $a['para_to']])
            ->update(['book_name' => $name.'_'.$a['chapter']]);
    }

    $after = $pairedParagraphs($to, $name);

    // 加对了后缀这个数应当上升或持平；下跌就是把真配对拆散了，整名回滚。
    $tolerance = (int) max(5, $before * 0.02);
    if ($after < $before - $tolerance) {
        foreach ($entry['assignments'] as $a) {
            DB::table($to)
                ->where('book', $a['book'])
                ->where('book_name', $name.'_'.$a['chapter'])
                ->whereBetween('para', [$a['para_from'], $a['para_to']])
                ->update(['book_name' => $name]);
        }
        $reverted++;
        $log->warn(sprintf('%-8s 回滚：可信配对 %d → %d 段，跌幅超容差 %d', $name, $before, $after, $tolerance));
        $log->unresolved('chapter_suffix_reverted', [
            'book_name' => $name, 'paired_before' => $before, 'paired_after' => $after,
            'chapters' => $entry['chapters'], 'confidence' => $entry['confidence'],
            'detail' => '加章后缀后可信配对大幅下跌，已回滚；多半是并列的两本书被当成了顺序拼接，待补丁或 LLM 判定',
        ]);
        $log->count('name.reverted');

        continue;
    }

    $applied++;
    $rowsChanged += $changed;
    $log->info(sprintf('%-8s %d 章，改名 %-6d 行，可信配对 %d → %d 段',
        $name, $entry['chapters'], $changed, $before, $after));
    $log->count('rows.renamed', $changed);
}

$log->info("加后缀的 book_name：{$applied} 个，共改名 {$rowsChanged} 行；因配对下跌回滚 {$reverted} 个");

$log->info('建索引中…');
DB::statement("create index on {$to} (book)");
DB::statement("create index on {$to} (para)");
DB::statement("create index on {$to} (book_name)");
DB::statement("create index on {$to} (book_name, cs_para)");
DB::statement("create index on {$to} (book, para, cs_para)");

$log->finish();
