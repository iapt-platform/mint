<?php

namespace App\Console\Commands;

use App\Models\RelatedParagraph;
use App\Services\RelatedParagraph\ParagraphMapBuilder;
use App\Services\RelatedParagraph\RelatedParagraphChecker;
use App\Services\RelatedParagraph\RelatedParagraphSource;
use App\Tools\Tools;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * 重建段落关联表 related_paragraphs（根本 ↔ 义注 ↔ 复注的段落对应）。
 *
 * 唯一数据源是 wbw_templates 的 .ctl. 锚点；sn 书名表与每个 book 的补丁表在
 * resources/data/related-paragraphs/。不再读 cs6_para.csv。
 * 设计与 book_name 状况表见 documents/related-paragraphs-rebuild.md。
 *
 *   php artisan upgrade:related.paragraph                    全量重建
 *   php artisan upgrade:related.paragraph --book=108 --book=145   只重建这几本（只删这几本）
 *   php artisan upgrade:related.paragraph --check [--book=…] [--json=report.json]   只算不写
 *   php artisan upgrade:related.paragraph --dump --book=108  导出未套补丁的结果（补丁表格式）
 */
class UpgradeRelatedParagraph extends Command
{
    protected $signature = 'upgrade:related.paragraph
        {--book=* : 只处理这些 VRI book}
        {--check : 只算不写，输出按 book_name 汇总的体检报告}
        {--dump : 把一本书未套补丁的重建结果按补丁表格式输出到 stdout}
        {--json= : 体检报告另存为 JSON（含回绕位置、孤儿号、告警）}';

    protected $description = '从 wbw_templates 重建 related_paragraphs';

    public function handle(RelatedParagraphSource $source, ParagraphMapBuilder $builder, RelatedParagraphChecker $checker): int
    {
        if (Tools::isStop()) {
            return self::SUCCESS;
        }
        ini_set('memory_limit', '2G');

        $books = array_map('intval', $this->option('book')) ?: $source->books();

        if ($this->option('dump')) {
            return $this->dump($source, $builder, $books);
        }

        $snNames = $source->snNames();
        $results = [];
        $report = [];
        $diff = [];

        foreach ($books as $book) {
            $paragraphs = $source->paragraphs($book);
            $markers = $source->markers($book);
            $titles = $source->titles($book);
            $patch = $source->patch($book);

            $missing = array_diff(array_keys($patch), $paragraphs);
            if ($missing !== []) {
                throw new RuntimeException("{$book}.csv 里的段落在 pali_texts 中不存在：".implode(',', $missing));
            }

            $built = $builder->build($paragraphs, $markers, $titles, $snNames, $patch);
            $report[$book] = [
                'warnings' => $built['warnings'],
                'cleared' => count(array_filter($built['rows'], fn ($row) => $row['cleared'])),
                'patch_rows' => count($patch),
                'patch_noop' => $patch === [] ? 0 : $this->countNoop($builder->build($paragraphs, $markers, $titles, $snNames)['rows'], $built['rows'], $patch),
            ];
            foreach ($this->diffAgainstTable($book, $built['rows']) as $name => $count) {
                $diff[$name] = ($diff[$name] ?? 0) + $count;
            }

            if (! $this->option('check')) {
                $this->write($book, $built['rows']);
            }

            $results[$book] = array_map(
                fn (array $row) => ['book_name' => $row['book_name'], 'begin' => $row['begin'], 'end' => $row['end'], 'book_id' => $row['book_id']],
                $built['rows'],
            );
        }

        $summary = $checker->summarize($results, $source->layers());
        $this->printSummary($summary, $diff);
        $this->printBookReport($report);

        if ($path = $this->option('json')) {
            file_put_contents($path, json_encode(
                ['book_names' => $summary, 'books' => $report, 'diff' => $diff],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE,
            ));
            $this->info("报告已写入 {$path}");
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<int>  $books
     */
    private function dump(RelatedParagraphSource $source, ParagraphMapBuilder $builder, array $books): int
    {
        if (count($books) !== 1) {
            $this->error('--dump 需要且只能指定一个 --book');

            return self::FAILURE;
        }

        $book = $books[0];
        $built = $builder->build($source->paragraphs($book), $source->markers($book), $source->titles($book), $source->snNames());
        $this->line('para,book_name,begin,end');
        foreach ($built['rows'] as $para => $row) {
            $this->line("{$para},{$row['book_name']},{$row['begin']},{$row['end']}");
        }

        return self::SUCCESS;
    }

    /**
     * 一本书在一个事务里删旧行、批量写新行。号段展开成多行，号为 0 的段落写一行 cs_para=0。
     *
     * @param  array<int, array{book_name: string, begin: int, end: int, book_id: int}>  $rows
     */
    private function write(int $book, array $rows): void
    {
        $now = now();
        DB::transaction(function () use ($book, $rows, $now) {
            RelatedParagraph::query()->where('book', $book)->delete();

            $batch = [];
            foreach ($rows as $para => $row) {
                foreach (range($row['begin'], $row['end']) as $csPara) {
                    $batch[] = [
                        'book' => $book,
                        'para' => $para,
                        'book_id' => $row['book_id'],
                        'cs_para' => $csPara,
                        'book_name' => $row['book_name'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    if (count($batch) === 2000) {
                        RelatedParagraph::query()->insert($batch);
                        $batch = [];
                    }
                }
            }
            if ($batch !== []) {
                RelatedParagraph::query()->insert($batch);
            }
        });
        $this->info("book {$book}: ".count($rows).' 段');
    }

    /**
     * 补丁里与重建值完全相同的行（多余，可删）。
     *
     * @param  array<int, array<string, mixed>>  $unpatched
     * @param  array<int, array<string, mixed>>  $patched
     * @param  array<int, mixed>  $patch
     */
    private function countNoop(array $unpatched, array $patched, array $patch): int
    {
        $noop = 0;
        foreach (array_keys($patch) as $para) {
            $before = $unpatched[$para];
            $after = $patched[$para];
            if ($before['book_name'] === $after['book_name'] && $before['begin'] === $after['begin'] && $before['end'] === $after['end']) {
                $noop++;
            }
        }

        return $noop;
    }

    /**
     * 与现表逐段比较，按 book_name 计不同的段落数（名字或号段任一不同）。
     *
     * @param  array<int, array{book_name: string, begin: int, end: int}>  $rows
     * @return array<string, int>
     */
    private function diffAgainstTable(int $book, array $rows): array
    {
        $current = RelatedParagraph::query()
            ->where('book', $book)
            ->groupBy('para')
            ->selectRaw('para, min(book_name) as book_name, min(cs_para) as cs_begin, max(cs_para) as cs_end')
            ->get()
            ->keyBy('para');

        $diff = [];
        foreach ($rows as $para => $row) {
            $old = $current->get($para);
            $same = $old !== null
                && (int) $old->cs_begin === $row['begin']
                && (int) $old->cs_end === $row['end']
                && ($row['begin'] === 0 || $old->book_name === $row['book_name']);
            if (! $same) {
                $name = $row['book_name'] !== '' ? $row['book_name'] : ($old->book_name ?? '');
                $diff[$name] = ($diff[$name] ?? 0) + 1;
            }
        }

        return $diff;
    }

    /**
     * @param  array<string, array<string, mixed>>  $summary
     * @param  array<string, int>  $diff
     */
    private function printSummary(array $summary, array $diff): void
    {
        $table = [];
        foreach ($summary as $name => $item) {
            $books = [];
            $wraps = [];
            $orphans = [];
            foreach ($item['books'] as $book => $stat) {
                $books[] = "{$book}({$stat['layer']})";
                $wraps[] = count($stat['wraps']);
                $orphans[] = count($stat['orphans']);
            }
            $table[] = [
                $name,
                implode(' ', $books),
                array_sum($wraps) > 0 ? '<fg=red>'.implode('/', $wraps).'</>' : '0',
                array_sum($orphans) > 0 ? implode('/', $orphans) : '0',
                $item['mula_max'] ?? '-',
                $diff[$name] ?? 0,
            ];
        }
        $this->table(['book_name', 'books(层级)', '回绕', '孤儿号', '根本最大号', '与现表不同的段'], $table);
    }

    /**
     * @param  array<int, array{warnings: list<string>, cleared: int, patch_rows: int, patch_noop: int}>  $report
     */
    private function printBookReport(array $report): void
    {
        foreach ($report as $book => $item) {
            foreach ($item['warnings'] as $warning) {
                $this->warn("book {$book} {$warning}");
            }
            if ($item['patch_noop'] > 0) {
                $this->warn("book {$book}：补丁 {$item['patch_rows']} 行里有 {$item['patch_noop']} 行与重建值相同（多余）");
            }
        }

        $cleared = array_filter(array_map(fn ($item) => $item['cleared'], $report));
        if ($cleared !== []) {
            $this->line('有号没名字、已清成 0 的段落（book: 段数）：'.implode('，', array_map(
                fn ($book, $count) => "{$book}: {$count}",
                array_keys($cleared),
                $cleared,
            )));
        }
    }
}
