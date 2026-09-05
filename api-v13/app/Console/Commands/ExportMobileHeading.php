<?php

namespace App\Console\Commands;

use App\Models\PaliText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 导出移动端离线目录库（SQLite）。
 *
 * 替代 wikipali-mobile 仓库里的 src/data/tipitaka_heading.json（5.5MB，
 * 全量载入内存）。导出 pali_texts 全部段落（不只是章节行，下载功能需要
 * 非章节段落的字符数），除原有目录字段外额外带上：

 *  - length：段落字符数（源表列名 `lenght` 为拼写错误，导出时纠正）
 *
 *  - tags：pali_texts.uid 经 tag_maps / tags 取到的标签名，逗号分隔
 *  - cs_para + book_name：来自 related_paragraphs，用于由段落定位对应的
 *    义注 / 复注章节起始位置（已合并进同一张表，输出只有 pali_text 一张表）
 */
class ExportMobileHeading extends Command
{
    protected $signature = 'export:mobile.heading
        {--out= : 输出文件路径，默认 storage/app/public/export/mobile/tipitaka-heading-<date>.db3}
        {--copy-to= : 导出后额外复制一份到该路径（例如移动端仓库的 assets/db/tipitaka.db3）}';

    protected $description = '导出移动端离线目录 SQLite（含 tags 与义注复注关联段落）';

    public function handle(): int
    {
        $out = $this->option('out');
        if (! $out) {
            $dir = storage_path('app/public/export/mobile');
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            $out = $dir.'/tipitaka-heading-'.date('Y-m-d').'.db3';
        }
        if (file_exists($out)) {
            unlink($out);
        }

        $dbh = new \PDO('sqlite:'.$out, '', '', [\PDO::ATTR_PERSISTENT => true]);
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->createSchema($dbh);

        $texts = $this->exportPaliTexts($dbh);

        $this->writeMeta($dbh, [
            'generated_at' => date('c'),
            'source' => config('app.url', ''),
            'pali_text_rows' => (string) $texts,
        ]);

        // 建索引放在插入之后，避免边插边维护索引
        $this->createIndexes($dbh);
        $dbh->exec('VACUUM');
        $dbh = null;

        $this->newLine();
        $this->info(sprintf(
            '导出完成：%s（%s，pali_text %d 行）',
            $out,
            $this->humanSize(filesize($out)),
            $texts
        ));

        $copyTo = $this->option('copy-to');
        if ($copyTo) {
            $dir = dirname($copyTo);
            if (! is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
            copy($out, $copyTo);
            $this->info('已复制到：'.$copyTo);
        }

        return 0;
    }

    private function createSchema(\PDO $dbh): void
    {
        // 全量段落（不只是章节标题行）：下载功能需要非章节段落的字符数。
        // 关联注释书（cs_para / book_name）已合并进本表 —— 每个段落至多对应
        // 一部注释书，无对应时为 NULL。
        $dbh->exec('CREATE TABLE pali_text (
            book INTEGER NOT NULL,
            paragraph INTEGER NOT NULL,
            level INTEGER NOT NULL,
            toc TEXT,
            length INTEGER,
            chapter_len INTEGER,
            chapter_strlen INTEGER,
            parent INTEGER,
            tags TEXT,
            cs_para INTEGER,
            book_name TEXT,
            PRIMARY KEY (book, paragraph)
        )');

        $dbh->exec('CREATE TABLE meta (key TEXT PRIMARY KEY, value TEXT)');
    }

    private function createIndexes(\PDO $dbh): void
    {
        $dbh->exec('CREATE INDEX idx_pali_text_book_level ON pali_text (book, level)');
        $dbh->exec('CREATE INDEX idx_pali_text_parent ON pali_text (book, parent)');
        $dbh->exec('CREATE INDEX idx_pali_text_related ON pali_text (book_name, cs_para)');
    }

    private function exportPaliTexts(\PDO $dbh): int
    {
        $total = PaliText::count();
        $this->line("导出 pali_text（全量段落）：{$total} 行");
        $bar = $this->output->createProgressBar($total);
        $bar->setRedrawFrequency(5000);

        // uid 只用于 join 标签，不写入结果表
        $tagsByUid = $this->loadTags();
        $related = $this->loadRelated();

        $stmt = $dbh->prepare('INSERT INTO pali_text
            (book, paragraph, level, toc, length, chapter_len, chapter_strlen,
             parent, tags, cs_para, book_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $dbh->beginTransaction();
        $n = 0;
        foreach (
            PaliText::select(['uid', 'book', 'paragraph', 'level', 'toc',
                'lenght', 'chapter_len', 'chapter_strlen', 'parent'])
                ->orderBy('book')
                ->orderBy('paragraph')
                ->cursor() as $row
        ) {
            $rel = $related[$row->book.'-'.$row->paragraph] ?? null;
            $stmt->execute([
                $row->book,
                $row->paragraph,
                $row->level,
                $row->toc !== '' ? $row->toc : null,
                // 源表列名 `lenght` 是拼写错误，导出时纠正为 `length`
                $row->lenght,
                $row->chapter_len,
                $row->chapter_strlen,
                $row->parent,
                isset($tagsByUid[$row->uid]) ? implode(',', $tagsByUid[$row->uid]) : null,
                $rel[0] ?? null,
                $rel[1] ?? null,
            ]);
            $n++;
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        $this->newLine();

        return $n;
    }

    /**
     * 关联注释书：由段落 (book, para) 找到对应义注 / 复注的起始位置。
     *
     * related_paragraphs 中同一段落会有多行，映射到一段连续的 cs_para 区间，
     * 但 book_name 唯一，因此这里取 min(cs_para) 作为起始位置合并成一行。
     * book_name 为空表示该段落没有对应的注释书，直接跳过（结果列留 NULL）。
     *
     * @return array<string, array{0:int,1:string}> "book-para" => [cs_para, book_name]
     */
    private function loadRelated(): array
    {
        $this->line('载入 related_paragraphs …');
        $out = [];
        DB::table('related_paragraphs')
            ->select(['book', 'para', 'book_name', DB::raw('min(cs_para) as cs_para')])
            ->whereNotNull('book_name')
            ->where('book_name', '<>', '')
            ->groupBy('book', 'para', 'book_name')
            ->orderBy('book')
            ->orderBy('para')
            ->chunk(50000, function ($rows) use (&$out) {
                foreach ($rows as $r) {
                    $out[$r->book.'-'.$r->para] = [(int) $r->cs_para, $r->book_name];
                }
            });
        $this->line('  有注释书的段落：'.count($out));

        return $out;
    }

    /** @return array<string, string[]> uid => tag names */
    private function loadTags(): array
    {
        $this->line('载入 tag_maps / tags …');
        $out = [];
        DB::table('tag_maps')
            ->join('tags', 'tags.id', '=', 'tag_maps.tag_id')
            ->where('tag_maps.table_name', 'pali_texts')
            ->select(['tag_maps.anchor_id', 'tags.name'])
            ->orderBy('tag_maps.anchor_id')
            ->chunk(20000, function ($rows) use (&$out) {
                foreach ($rows as $r) {
                    $out[$r->anchor_id][] = $r->name;
                }
            });
        $this->line('  标签锚点：'.count($out));

        return $out;
    }

    private function writeMeta(\PDO $dbh, array $meta): void
    {
        $stmt = $dbh->prepare('INSERT INTO meta (key, value) VALUES (?, ?)');
        foreach ($meta as $k => $v) {
            $stmt->execute([$k, $v]);
        }
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
