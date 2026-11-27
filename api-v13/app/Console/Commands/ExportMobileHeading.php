<?php

namespace App\Console\Commands;

use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\RelatedParagraph;
use Illuminate\Console\Command;

/**
 * 导出移动端离线目录库（SQLite）。
 *
 * 替代 wikipali-mobile 仓库里的 src/data/tipitaka_heading.json（5.5MB，
 * 全量载入内存）。导出 pali_texts 全部段落（不只是章节行，下载功能需要
 * 非章节段落的字符数）。输出两张表：
 *
 *  - pali_text：全量段落，除原有目录字段外额外带上
 *      - length：段落字符数（源表列名 `lenght` 为拼写错误，导出时纠正）
 *      - cs_para + book_name：来自 related_paragraphs，用于由段落定位对应的
 *        义注 / 复注章节起始位置
 *  - works：作品清单，来自 book_titles（一行 = 一部作品，起点段 (book, paragraph)，
 *    多为 level=1，少数是 level=2，如书 209 Vajirabuddhi-ṭīkā 拆成的各篇）。
 *    tags 只挂在作品上：作品起点段的 pali_texts.uid 经 tag_maps / tags 取到的
 *    标签名，逗号分隔。移动端靠它判断注释层次。
 */
class ExportMobileHeading extends Command
{
    protected $signature = 'export:mobile.heading
        {--out= : 输出文件路径，默认 storage/app/public/export/mobile/tipitaka-heading-<date>.db3}
        {--copy-to= : 导出后额外复制一份到该路径（例如移动端仓库的 assets/db/tipitaka.db3）}';

    protected $description = '导出移动端离线目录 SQLite（段落 + 作品表 works，含 tags 与义注复注关联段落）';

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
        $works = $this->exportWorks($dbh);

        $this->writeMeta($dbh, [
            'generated_at' => date('c'),
            'source' => config('app.url', ''),
            'pali_text_rows' => (string) $texts,
            'works_rows' => (string) $works,
        ]);

        // 建索引放在插入之后，避免边插边维护索引
        $this->createIndexes($dbh);
        $dbh->exec('VACUUM');
        $dbh = null;

        $this->newLine();
        $this->info(sprintf(
            '导出完成：%s（%s，pali_text %d 行，works %d 行）',
            $out,
            $this->humanSize(filesize($out)),
            $texts,
            $works
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
            cs_para INTEGER,
            book_name TEXT,
            PRIMARY KEY (book, paragraph)
        )');

        // 作品：book_titles 一行一部。作品的段落范围 = [paragraph, 同 book 下一部
        // 作品的 paragraph - 1]，客户端按此推算，不另存。
        $dbh->exec('CREATE TABLE works (
            sn INTEGER PRIMARY KEY,
            book INTEGER NOT NULL,
            paragraph INTEGER NOT NULL,
            level INTEGER,
            title TEXT,
            toc TEXT,
            tags TEXT
        )');

        $dbh->exec('CREATE TABLE meta (key TEXT PRIMARY KEY, value TEXT)');
    }

    private function createIndexes(\PDO $dbh): void
    {
        $dbh->exec('CREATE INDEX idx_pali_text_book_level ON pali_text (book, level)');
        $dbh->exec('CREATE INDEX idx_pali_text_parent ON pali_text (book, parent)');
        $dbh->exec('CREATE INDEX idx_pali_text_related ON pali_text (book_name, cs_para)');
        $dbh->exec('CREATE UNIQUE INDEX idx_works_book_para ON works (book, paragraph)');
    }

    private function exportPaliTexts(\PDO $dbh): int
    {
        $total = PaliText::count();
        $this->line("导出 pali_text（全量段落）：{$total} 行");
        $bar = $this->output->createProgressBar($total);
        $bar->setRedrawFrequency(5000);

        $related = $this->loadRelated();

        $stmt = $dbh->prepare('INSERT INTO pali_text
            (book, paragraph, level, toc, length, chapter_len, chapter_strlen,
             parent, cs_para, book_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

        $dbh->beginTransaction();
        $n = 0;
        foreach (
            PaliText::select(['book', 'paragraph', 'level', 'toc',
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
     * book_name 为空或 cs_para 为 0 表示该段落没有对应段落，直接跳过（结果列留 NULL）。
     * 0 不能照抄：mobile 只把 NULL 当「无对应」，0 会被当成坐标去配同名所有 0 号段落
     * （related_paragraphs 重建后，复注自己的序言、标题段等都是 0）。
     *
     * @return array<string, array{0:int,1:string}> "book-para" => [cs_para, book_name]
     */
    private function loadRelated(): array
    {
        $this->line('载入 related_paragraphs …');
        $out = [];
        RelatedParagraph::query()
            ->selectRaw('book, para, book_name, min(cs_para) as cs_para')
            ->whereNotNull('book_name')
            ->where('book_name', '<>', '')
            ->where('cs_para', '>', 0)
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

    /**
     * 作品表：book_titles 一行一部，tags 取作品起点段（pali_texts 同 book/paragraph
     * 那一行）的 tag_maps → tags。按 sn 排序；同 (book, paragraph) 重复的只留第一条。
     */
    private function exportWorks(\PDO $dbh): int
    {
        $this->line('导出 works（book_titles）…');
        $titles = BookTitle::orderBy('sn')->get(['sn', 'book', 'paragraph', 'title']);

        $stmt = $dbh->prepare('INSERT INTO works (sn, book, paragraph, level, title, toc, tags)
            VALUES (?, ?, ?, ?, ?, ?, ?)');

        $dbh->beginTransaction();
        $seen = [];
        $n = 0;
        foreach ($titles as $bt) {
            $key = $bt->book.'-'.$bt->paragraph;
            if (isset($seen[$key])) {
                $this->warn("  book_titles 重复：{$key}（sn={$bt->sn}），跳过");

                continue;
            }
            $seen[$key] = true;

            $text = PaliText::where('book', $bt->book)
                ->where('paragraph', $bt->paragraph)
                ->with(['tagMaps' => fn ($q) => $q->where('table_name', 'pali_texts'), 'tagMaps.tags'])
                ->first(['uid', 'level', 'toc']);
            if (! $text) {
                $this->warn("  pali_texts 找不到作品起点段：{$key}（sn={$bt->sn}）");
            }
            $tags = $text
                ? $text->tagMaps->map(fn ($m) => $m->tags?->name)->filter()->unique()->sort()->values()->all()
                : [];

            $stmt->execute([
                $bt->sn,
                $bt->book,
                $bt->paragraph,
                $text?->level,
                $bt->title,
                $text && $text->toc !== '' ? $text->toc : null,
                $tags ? implode(',', $tags) : null,
            ]);
            $n++;
        }
        $dbh->commit();
        $this->line("  作品：{$n}");

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
