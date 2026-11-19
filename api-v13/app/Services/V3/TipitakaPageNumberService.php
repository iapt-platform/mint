<?php

namespace App\Services\V3;

use App\Http\Api\BookTitle as EditionBookTitles;
use App\Models\PageNumber;
use App\Models\WbwTemplate;

/**
 * 按印本页码找三藏段落：PTS、缅甸第六次结集版、VRI、泰国版、其他，以及 CS 版印的段落号。
 *
 * 印本页码在 `page_numbers`（每条是正文里一个页码标记的位置）；CS 段落号在逐词表里
 * （`real = para{n}` 的词元）。一个页码在各版本里加起来最多约一千处，不分页。
 */
class TipitakaPageNumberService
{
    /** page_numbers.type 代号 → 对外的版本名（与 tipitaka-word-paragraphs 的 refs.type 一致） */
    public const EDITIONS = ['P' => 'PTS', 'M' => 'My', 'V' => 'VRI', 'T' => 'Thai', 'O' => 'Other'];

    /** CS 版印的段落号 */
    public const CS = 'CS';

    /** 版本 → 静态书名表里该版本的书名字段（v6 印本页阅读页按它定位，同 QuoteLinkLoader） */
    private const TITLE_FIELDS = ['PTS' => 'p_title', 'My' => 'm_title', 'VRI' => 'v_title'];

    public function __construct(private readonly TipitakaBookService $books) {}

    /**
     * @param  array{edition?: string, volume?: int}  $filters
     * @return list<array{
     *     edition: string, volume: int|null, page: int, book: int, para: int,
     *     book_id: int, book_title: string, book_para: int, edition_title: string|null, layer: string|null, tags: list<string>
     * }>
     */
    public function find(int $page, array $filters): array
    {
        $edition = $filters['edition'] ?? null;
        $volume = $filters['volume'] ?? null;
        $rows = [];

        if ($edition !== self::CS) {
            $types = $edition === null ? array_keys(self::EDITIONS) : [array_search($edition, self::EDITIONS, true)];
            $pages = PageNumber::query()
                ->where('page', $page)
                ->whereIn('type', $types)
                ->when($volume !== null, fn ($q) => $q->where('volume', $volume))
                ->get(['type', 'volume', 'page', 'book', 'paragraph', 'pcd_book_id']);
            foreach ($pages as $p) {
                $rows[] = [
                    'edition' => self::EDITIONS[$p->type],
                    // 0 是「卷号未知」
                    'volume' => (int) $p->volume ?: null,
                    'page' => (int) $p->page,
                    'book' => (int) $p->book,
                    'para' => (int) $p->paragraph,
                    'book_id' => (int) $p->pcd_book_id,
                ];
            }
        }

        // CS 段落号不分卷：给了卷号就不查
        if (($edition === null || $edition === self::CS) && $volume === null) {
            $paras = WbwTemplate::where('real', "para{$page}")->get(['book', 'paragraph', 'pcd_book_id']);
            foreach ($paras as $p) {
                $rows[] = [
                    'edition' => self::CS,
                    'volume' => null,
                    'page' => $page,
                    'book' => (int) $p->book,
                    'para' => (int) $p->paragraph,
                    'book_id' => (int) $p->pcd_book_id,
                ];
            }
        }

        $books = $this->books->find(array_column($rows, 'book_id'));
        $editionTitles = collect(EditionBookTitles::get())->keyBy('id');

        $rows = array_map(function (array $row) use ($books, $editionTitles) {
            $book = $books->get($row['book_id']);
            $titles = $editionTitles->get($row['book_id']);
            $field = self::TITLE_FIELDS[$row['edition']] ?? 'term';

            return $row + [
                'book_title' => $book['title'] ?? '',
                'book_para' => $book['para'] ?? 0,
                'edition_title' => $row['edition'] === self::CS ? null : ($titles[$field] ?? null),
                'layer' => $book['layer'] ?? null,
                'tags' => $book['tags'] ?? [],
            ];
        }, $rows);

        // 按版本（PTS、缅、VRI、泰、其他、CS）、书、卷、段排
        $order = array_flip([...array_values(self::EDITIONS), self::CS]);
        usort($rows, fn (array $a, array $b) => [$order[$a['edition']], $a['book_id'], $a['volume'] ?? 0, $a['book'], $a['para']]
            <=> [$order[$b['edition']], $b['book_id'], $b['volume'] ?? 0, $b['book'], $b['para']]);

        return $rows;
    }
}
