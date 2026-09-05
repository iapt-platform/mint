<?php

namespace App\Console\Commands;

use App\Models\PaliText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * 导出移动端离线目录库（SQLite）。
 *
 * 替代 wikipali-mobile 仓库里的 src/data/tipitaka_heading.json（5.5MB，
 * 全量载入内存）。除原有目录字段外，额外带上：
 *
 *  - tags：pali_texts.uid 经 tag_maps / tags 取到的标签名，逗号分隔
 *  - related_paragraphs：cs_para + book_name，用于由根本章节定位
 *    对应的义注 / 复注章节起始位置
 */
class ExportMobileHeading extends Command
{
    protected $signature = 'export:mobile.heading
        {--out= : 输出文件路径，默认 storage/app/public/export/mobile/tipitaka-heading-<date>.db3}
        {--max-level=7 : 只导出 level <= 该值的行（与现有 tipitaka_heading.json 口径一致）}
        {--copy-to= : 导出后额外复制一份到该路径（例如移动端仓库的 assets/db/tipitaka.db3）}';

    protected $description = '导出移动端离线目录 SQLite（含 tags 与义注复注关联段落）';

    public function handle(): int
    {
        $maxLevel = (int) $this->option('max-level');

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

        $headings = $this->exportHeadings($dbh, $maxLevel);
        $related = $this->exportRelated($dbh, $maxLevel);

        $this->writeMeta($dbh, [
            'generated_at' => date('c'),
            'source' => config('app.url', ''),
            'max_level' => (string) $maxLevel,
            'heading_rows' => (string) $headings,
            'related_rows' => (string) $related,
        ]);

        // 建索引放在插入之后，避免边插边维护索引
        $this->createIndexes($dbh);
        $dbh->exec('VACUUM');
        $dbh = null;

        $this->newLine();
        $this->info(sprintf(
            '导出完成：%s（%s，heading %d 行，related %d 行）',
            $out,
            $this->humanSize(filesize($out)),
            $headings,
            $related
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
        $dbh->exec('CREATE TABLE heading (
            book INTEGER NOT NULL,
            paragraph INTEGER NOT NULL,
            level INTEGER NOT NULL,
            toc TEXT,
            chapter_len INTEGER,
            chapter_strlen INTEGER,
            parent INTEGER,
            uid TEXT,
            tags TEXT,
            PRIMARY KEY (book, paragraph)
        )');

        // 一个根本章节可关联多部义注 / 复注，故独立成表
        $dbh->exec('CREATE TABLE related_paragraph (
            book INTEGER NOT NULL,
            para INTEGER NOT NULL,
            book_id INTEGER,
            cs_para INTEGER,
            book_name TEXT
        )');

        $dbh->exec('CREATE TABLE meta (key TEXT PRIMARY KEY, value TEXT)');
    }

    private function createIndexes(\PDO $dbh): void
    {
        $dbh->exec('CREATE INDEX idx_heading_book_level ON heading (book, level)');
        $dbh->exec('CREATE INDEX idx_heading_parent ON heading (book, parent)');
        $dbh->exec('CREATE INDEX idx_related_src ON related_paragraph (book, para)');
        $dbh->exec('CREATE INDEX idx_related_dst ON related_paragraph (book_name, cs_para)');
    }

    private function exportHeadings(\PDO $dbh, int $maxLevel): int
    {
        $total = PaliText::where('level', '<=', $maxLevel)->count();
        $this->line("导出 heading（level <= {$maxLevel}）：{$total} 行");
        $bar = $this->output->createProgressBar($total);

        // uid -> 标签名列表。tag_maps.table_name 对 pali_texts 使用复数表名。
        $tagsByUid = $this->loadTags();

        $stmt = $dbh->prepare('INSERT INTO heading
            (book, paragraph, level, toc, chapter_len, chapter_strlen, parent, uid, tags)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $dbh->beginTransaction();
        $n = 0;
        foreach (
            PaliText::where('level', '<=', $maxLevel)
                ->select(['uid', 'book', 'paragraph', 'level', 'toc',
                    'chapter_len', 'chapter_strlen', 'parent'])
                ->orderBy('book')
                ->orderBy('paragraph')
                ->cursor() as $row
        ) {
            $stmt->execute([
                $row->book,
                $row->paragraph,
                $row->level,
                $row->toc,
                $row->chapter_len,
                $row->chapter_strlen,
                $row->parent,
                $row->uid,
                isset($tagsByUid[$row->uid]) ? implode(',', $tagsByUid[$row->uid]) : null,
            ]);
            $n++;
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        $this->newLine();

        return $n;
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

    /**
     * 只导出「源段落本身是被导出的 heading」的关联行 ——
     * 移动端是按章节查关联，非章节段落的关联行用不到。
     */
    private function exportRelated(\PDO $dbh, int $maxLevel): int
    {
        $sub = DB::table('pali_texts')
            ->select(['book', 'paragraph'])
            ->where('level', '<=', $maxLevel);

        $query = DB::table('related_paragraphs as r')
            ->joinSub($sub, 'h', function ($join) {
                $join->on('h.book', '=', 'r.book')->on('h.paragraph', '=', 'r.para');
            })
            ->select(['r.book', 'r.para', 'r.book_id', 'r.cs_para', 'r.book_name']);

        $total = $query->count();
        $this->line("导出 related_paragraph：{$total} 行");
        $bar = $this->output->createProgressBar($total);

        $stmt = $dbh->prepare('INSERT INTO related_paragraph
            (book, para, book_id, cs_para, book_name) VALUES (?, ?, ?, ?, ?)');

        $dbh->beginTransaction();
        $n = 0;
        foreach ($query->orderBy('r.book')->orderBy('r.para')->cursor() as $row) {
            $stmt->execute([$row->book, $row->para, $row->book_id, $row->cs_para, $row->book_name]);
            $n++;
            $bar->advance();
        }
        $dbh->commit();
        $bar->finish();
        $this->newLine();

        return $n;
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
