<?php

namespace App\Services\V3;

use App\Models\PageNumber;
use App\Models\PaliText;
use App\Models\WbwTemplate;
use App\Services\PaliSeriesesService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 按词形找三藏段落（逐词表 `wbw_templates`：连音已拆开，每个词形精确匹配）。
 *
 * 与 OpenSearch 全文检索（`/v3/search`）互补：这里召回精确到词形、能区分黑体（注释书里
 * 被解释的词），但不做模糊、不做多词共现。
 *
 * v2 `search-pali-wbw` 每行另查 3～4 次库（段落、标题、页码、丛书缩写），这里整页批量取。
 */
class TipitakaWordParagraphService
{
    /** page_numbers.type 代号 → 对外的版本名（与 v2 ref.type 相同，插件按它拼出处） */
    private const EDITIONS = ['M' => 'My', 'P' => 'PTS', 'V' => 'VRI', 'T' => 'Thai', 'O' => 'Other'];

    public function __construct(
        private readonly PaliSeriesesService $serieses,
        private readonly TipitakaBookService $books,
    ) {}

    /**
     * 命中段落，按命中权重降序（同分按经文顺序）。
     *
     * @param  array{words: list<string>, bold: string|null, book_ids: list<int>}  $criteria
     * @return LengthAwarePaginator<int, array{
     *     book: int, para: int, score: int, hits: int, title: string, level: int,
     *     path: list<array{book: int, para: int, title: string, level: int}>,
     *     text: string,
     *     refs: list<array{type: string, volume: int|null, page: int, title: string|null}>
     * }>
     */
    public function paragraphs(array $criteria, int $perPage): LengthAwarePaginator
    {
        $page = $this->matches($criteria)
            ->when($criteria['book_ids'] !== [], fn (Builder $q) => $q->whereIn('pcd_book_id', $criteria['book_ids']))
            ->groupBy(['book', 'paragraph'])
            ->selectRaw('book, paragraph, sum(weight) as score, count(*) as hits')
            ->orderByDesc('score')
            ->orderBy('book')
            ->orderBy('paragraph')
            ->paginate($perPage);

        $rows = collect($page->items());
        $texts = $this->paliTexts($rows);
        $refs = $this->pageNumbers($rows);
        $highlighter = $this->highlighter($criteria['words']);

        return $page->setCollection($rows->map(function ($row) use ($texts, $refs, $highlighter) {
            $book = (int) $row->book;
            $para = (int) $row->paragraph;
            $text = $texts->get("{$book}-{$para}");
            $path = self::path($text?->path);
            $series = $this->serieses->find($book, $para);

            return [
                'book' => $book,
                'para' => $para,
                'score' => (int) $row->score,
                'hits' => (int) $row->hits,
                // 标题段（level < 100）用自己的标题，正文段用所在章节的标题
                'title' => $text && $text->level < 100 ? (string) $text->toc : (string) (end($path)['title'] ?? ''),
                'level' => (int) ($text?->level ?? 100),
                'path' => $path,
                'text' => $highlighter((string) ($text?->html ?? '')),
                'refs' => [
                    ...array_map(fn (array $r) => $r + ['title' => match ($r['type']) {
                        'My' => $series['abbr_my'] ?? null,
                        'PTS' => $series['abbr_pts'] ?? null,
                        default => null,
                    }], $refs->get("{$book}-{$para}", [])),
                    ['type' => 'WP', 'volume' => null, 'page' => $para, 'title' => $series['abbr_wp'] ?? null],
                ],
            ];
        }));
    }

    /**
     * 按书统计：每本书（pcd_book_id）命中多少段、多少词次。按段落数降序。
     *
     * @param  array{words: list<string>, bold: string|null}  $criteria
     * @return array{
     *     items: list<array{book_id: int, book: int, para: int, title: string, layer: string|null, tags: list<string>, paragraphs: int, occurrences: int}>,
     *     paragraphs: int,
     *     occurrences: int
     * }
     */
    public function books(array $criteria): array
    {
        $groups = $this->matches($criteria)
            ->groupBy('pcd_book_id')
            ->selectRaw('pcd_book_id, count(*) as occurrences, count(distinct (book, paragraph)) as paragraphs')
            ->orderByDesc('paragraphs')
            ->orderBy('pcd_book_id')
            ->get();

        $books = $this->books->find($groups->pluck('pcd_book_id'));

        $items = $groups->map(function ($group) use ($books) {
            $book = $books->get((int) $group->pcd_book_id);

            return [
                'book_id' => (int) $group->pcd_book_id,
                'book' => $book['book'] ?? 0,
                'para' => $book['para'] ?? 0,
                'title' => $book['title'] ?? '',
                'layer' => $book['layer'] ?? null,
                'tags' => $book['tags'] ?? [],
                'paragraphs' => (int) $group->paragraphs,
                'occurrences' => (int) $group->occurrences,
            ];
        })->values()->all();

        return [
            'items' => $items,
            'paragraphs' => array_sum(array_column($items, 'paragraphs')),
            'occurrences' => array_sum(array_column($items, 'occurrences')),
        ];
    }

    /**
     * 词形与黑体条件。
     *
     * @param  array{words: list<string>, bold: string|null}  $criteria
     */
    private function matches(array $criteria): Builder
    {
        return WbwTemplate::query()
            ->whereIn('real', $criteria['words'])
            ->when($criteria['bold'] === 'only', fn (Builder $q) => $q->where('style', 'bld'))
            ->when($criteria['bold'] === 'exclude', fn (Builder $q) => $q->where(
                fn (Builder $q) => $q->where('style', '<>', 'bld')->orWhereNull('style'),
            ));
    }

    /**
     * 本页段落的正文与路径，一次查完。
     *
     * @param  Collection<int, mixed>  $rows
     * @return Collection<string, PaliText>
     */
    private function paliTexts(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        return PaliText::query()
            ->where(function (Builder $q) use ($rows) {
                foreach ($rows as $row) {
                    $q->orWhere(fn (Builder $q) => $q->where('book', $row->book)->where('paragraph', $row->paragraph));
                }
            })
            ->get(['book', 'paragraph', 'level', 'toc', 'path', 'html'])
            ->keyBy(fn (PaliText $t) => "{$t->book}-{$t->paragraph}");
    }

    /**
     * 本页段落在各印本里的页码，每种版本取段落里第一个页码标记。一次查完。
     *
     * @param  Collection<int, mixed>  $rows
     * @return Collection<string, list<array{type: string, volume: int|null, page: int}>>
     */
    private function pageNumbers(Collection $rows): Collection
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        return PageNumber::query()
            ->where(function (Builder $q) use ($rows) {
                foreach ($rows as $row) {
                    $q->orWhere(fn (Builder $q) => $q->where('book', $row->book)->where('paragraph', $row->paragraph));
                }
            })
            ->orderBy('wid')
            ->get(['type', 'volume', 'page', 'book', 'paragraph'])
            ->groupBy(fn (PageNumber $p) => "{$p->book}-{$p->paragraph}")
            ->map(fn (Collection $pages) => $pages->unique('type')->map(fn (PageNumber $p) => [
                'type' => self::EDITIONS[$p->type] ?? (string) $p->type,
                'volume' => $p->volume === null ? null : (int) $p->volume,
                'page' => (int) $p->page,
            ])->values()->all());
    }

    /**
     * `pali_texts.path`（JSON 字符串，level 有时是字符串）→ 规整的数组。
     *
     * @return list<array{book: int, para: int, title: string, level: int}>
     */
    private static function path(mixed $raw): array
    {
        $items = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_map(fn (array $p) => [
            'book' => (int) ($p['book'] ?? 0),
            'para' => (int) ($p['paragraph'] ?? 0),
            'title' => (string) ($p['title'] ?? ''),
            'level' => (int) ($p['level'] ?? 0),
        ], array_filter($items, 'is_array')));
    }

    /**
     * 正文 HTML → 只含 `<b>`（黑体）与 `<mark>`（命中词形）的安全 HTML。
     *
     * 其余标签全部去掉、正文全部转义，客户端可以直接作为 HTML 渲染。命中词按整词匹配
     * （前后不是字母），不区分首字母大小写；词形以 ti / nti 结尾的，正文里常写成
     * 「词’’ti」，所以也匹配去掉 ti 后的部分（同 v2）。
     *
     * @param  list<string>  $words
     * @return \Closure(string): string
     */
    private function highlighter(array $words): \Closure
    {
        $variants = [];
        foreach ($words as $word) {
            $variants[] = $word;
            if (str_ends_with($word, 'nti')) {
                $variants[] = mb_substr($word, 0, -3, 'UTF-8');
            } elseif (str_ends_with($word, 'ti')) {
                $variants[] = mb_substr($word, 0, -2, 'UTF-8');
            }
        }
        $variants = array_filter(array_unique($variants), fn (string $v) => mb_strlen($v, 'UTF-8') > 1);
        usort($variants, fn (string $a, string $b) => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));
        $alternatives = implode('|', array_map(fn (string $v) => preg_quote(htmlspecialchars($v, ENT_QUOTES, 'UTF-8'), '/'), $variants));
        $pattern = $alternatives === '' ? null : '/(?<![\p{L}\p{M}])('.$alternatives.')(?![\p{L}\p{M}])/iu';

        return function (string $html) use ($pattern): string {
            // 黑体先换成占位符，去标签、解实体、整体转义，再换回 <b>
            $text = preg_replace('/<span class=["\']bld["\']>(.*?)<\/span>/su', "\u{E000}$1\u{E001}", $html) ?? '';
            $text = htmlspecialchars(trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8');
            if ($pattern !== null) {
                $text = preg_replace($pattern, '<mark>$1</mark>', $text) ?? $text;
            }

            return str_replace(["\u{E000}", "\u{E001}"], ['<b>', '</b>'], $text);
        };
    }
}
