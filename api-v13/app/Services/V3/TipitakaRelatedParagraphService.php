<?php

namespace App\Services\V3;

use App\Models\BookTitle;
use App\Models\PaliText;
use App\Models\RelatedParagraph;
use App\Models\Tag;
use App\Models\TagMap;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * 段落关联关系（mūla / aṭṭhakathā / ṭīkā）的查询与聚合。
 *
 * 服务 `GET /v3/tipitaka-related-paragraphs`、`…/aggregate` 与 `…/aggregate/{book_name}`。
 * v2 的 `RelatedParagraphController` 另有一套实现（Resource 里逐行打 5 条 SQL），
 * 两边不共用——见 CLAUDE.md 铁律 2：契约相关的逻辑 v3 重写。
 *
 * 这里把旧 Resource 的逐行查询收敛成常量次批量查询：book_titles → pali_texts →
 * tag_maps → tags，无论命中几本书都是固定几条 SQL。
 */
class TipitakaRelatedParagraphService
{
    /**
     * 查一个段落的关联段落，按 book_id 分组，一本书一条。
     *
     * @param  array<string, mixed>  $filters  已经过 FormRequest 校验的查询参数
     * @return array<int, array{book: int, book_id: int, cs_para: int, para: array<int, int>, title: string|null, tags: array<int, string>, path: array|null}>
     */
    public function related(array $filters): array
    {
        // 1. 确定匹配键 (book_name, cs_para)
        if (isset($filters['book'])) {
            $anchor = RelatedParagraph::query()
                ->where('book', (int) $filters['book'])
                ->where('para', (int) $filters['para'])
                ->where('cs_para', '>', 0)
                ->select(['book_name', 'cs_para'])
                ->first();

            // 该段没有 cs6 锚点（约 2% 的段落如此），或 book/para 传错。
            // 「没有关联段落」是正常结果，不是错误——返回空集
            if (! $anchor) {
                return [];
            }

            $bookName = $anchor->book_name;
            $csPara = (int) $anchor->cs_para;
        } else {
            $bookName = $filters['book_name'];
            $csPara = (int) $filters['cs_para'];
        }

        // 2. 取所有命中行，按 book_id 分组
        $rows = RelatedParagraph::query()
            ->where('book_name', $bookName)
            ->where('cs_para', $csPara)
            ->when(isset($filters['book_id']), fn (Builder $q) => $q->where('book_id', (int) $filters['book_id']))
            ->orderBy('book_id')
            ->orderBy('para')
            ->get(['book', 'book_id', 'para']);

        if ($rows->isEmpty()) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $bookId = (int) $row->book_id;
            if (! isset($grouped[$bookId])) {
                $grouped[$bookId] = [
                    'book' => (int) $row->book,
                    'book_id' => $bookId,
                    'cs_para' => $csPara,
                    'para' => [],
                ];
            }
            $grouped[$bookId]['para'][] = (int) $row->para;
        }

        return array_values($this->enrich($grouped));
    }

    /**
     * 聚合：列出所有不同的 SC 缩写（去空、去重），分页返回。
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, RelatedParagraph>
     */
    public function bookNames(array $filters): LengthAwarePaginator
    {
        return RelatedParagraph::query()
            ->when(isset($filters['book']), fn (Builder $q) => $q->where('book', (int) $filters['book']))
            ->when(isset($filters['book_id']), fn (Builder $q) => $q->where('book_id', (int) $filters['book_id']))
            ->where('cs_para', '>', 0)
            ->where('book_name', '!=', '')
            ->select('book_name')
            ->groupBy('book_name')
            ->orderBy('book_name')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 聚合：列出某个 SC 缩写下的所有 cs_para（去重、>0），分页返回。
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, RelatedParagraph>
     */
    public function csParas(string $bookName, array $filters): LengthAwarePaginator
    {
        return RelatedParagraph::query()
            ->where('book_name', $bookName)
            ->when(isset($filters['book']), fn (Builder $q) => $q->where('book', (int) $filters['book']))
            ->when(isset($filters['book_id']), fn (Builder $q) => $q->where('book_id', (int) $filters['book_id']))
            ->where('cs_para', '>', 0)
            ->select('cs_para')
            ->groupBy('cs_para')
            ->orderBy('cs_para')
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * 批量补全每组 book_id 的书名、标签与目录路径。
     *
     * 旧 Resource 里每行打 BookTitle / PaliText(uid) / TagMap / Tag / PaliText(path)
     * 五条 SQL，这里统一按 id 集合一次查回再内存映射。
     *
     * @param  array<int, array<string, mixed>>  $grouped
     * @return array<int, array<string, mixed>>
     */
    private function enrich(array $grouped): array
    {
        $bookIds = array_keys($grouped);

        // 书名：book_id(sn) → book_titles
        $titles = BookTitle::query()
            ->whereIn('sn', $bookIds)
            ->get(['sn', 'book', 'paragraph', 'title'])
            ->keyBy('sn');

        // 需要查 pali_texts 的 (book, paragraph) 坐标：
        //   - 每组的书名行 → uid（供 tags）
        //   - 每组第一条 para → path
        $titleCoordByBookId = [];
        $pathCoordByBookId = [];
        $coordBooks = [];
        $coordParas = [];
        foreach ($grouped as $bookId => $group) {
            $title = $titles->get($bookId);
            if ($title) {
                $titleCoordByBookId[$bookId] = [(int) $title->book, (int) $title->paragraph];
            }
            $pathCoordByBookId[$bookId] = [$group['book'], $group['para'][0]];
        }
        foreach (array_merge(array_values($titleCoordByBookId), array_values($pathCoordByBookId)) as [$book, $paragraph]) {
            $coordBooks[] = $book;
            $coordParas[] = $paragraph;
        }

        $paliTexts = collect();
        if ($coordBooks !== []) {
            $paliTexts = PaliText::query()
                ->whereIn('book', array_values(array_unique($coordBooks)))
                ->whereIn('paragraph', array_values(array_unique($coordParas)))
                ->get(['book', 'paragraph', 'uid', 'path'])
                ->keyBy(fn ($pt) => $pt->book.':'.$pt->paragraph);
        }

        // 标签：pali_texts.uid → tag_maps → tags
        $uids = $paliTexts->pluck('uid')->filter()->unique()->all();
        $tagIdsByUid = [];
        if ($uids !== []) {
            foreach (TagMap::query()->whereIn('anchor_id', $uids)->get(['anchor_id', 'tag_id']) as $map) {
                $tagIdsByUid[$map->anchor_id][] = $map->tag_id;
            }
        }

        $tagIds = array_unique(array_merge([], ...array_values($tagIdsByUid)));
        $tags = $tagIds === []
            ? collect()
            : Tag::query()->whereIn('id', $tagIds)->get(['id', 'name'])->keyBy('id');

        foreach ($grouped as $bookId => &$group) {
            $group['title'] = $titles->get($bookId)?->title;

            $group['tags'] = [];
            if (isset($titleCoordByBookId[$bookId])) {
                [$titleBook, $titleParagraph] = $titleCoordByBookId[$bookId];
                $titleText = $paliTexts->get($titleBook.':'.$titleParagraph);
                if ($titleText) {
                    foreach ($tagIdsByUid[$titleText->uid] ?? [] as $tagId) {
                        if ($tags->has($tagId)) {
                            $group['tags'][] = $tags[$tagId]->name;
                        }
                    }
                }
            }

            [$pathBook, $pathParagraph] = $pathCoordByBookId[$bookId];
            $pathText = $paliTexts->get($pathBook.':'.$pathParagraph);
            $group['path'] = $pathText ? json_decode($pathText->path, true) : null;
        }
        unset($group);

        return $grouped;
    }
}
