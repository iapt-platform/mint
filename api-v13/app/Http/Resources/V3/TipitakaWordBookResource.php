<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 按词形检索的一本书的命中统计。
 *
 * `book_id` 是 `pcd_book_id`（`book_titles.sn`），也是列表端点 `book_id` 过滤用的值；
 * `book` / `para` 是书名所在段的坐标。`paragraphs` 是命中段落数，`occurrences` 是词次
 * （同一段出现多次算多次）。`layer` 取自书的 tag（mula / atthakatha / tika / anna），没有则为 null。
 */
class TipitakaWordBookResource extends BaseResource
{
    /**
     * @return array{
     *     book_id: int,
     *     book: int,
     *     para: int,
     *     title: string,
     *     layer: string|null,
     *     tags: list<string>,
     *     paragraphs: int,
     *     occurrences: int
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'book_id' => $this['book_id'],
            'book' => $this['book'],
            'para' => $this['para'],
            'title' => $this['title'],
            'layer' => $this['layer'],
            'tags' => $this['tags'],
            'paragraphs' => $this['paragraphs'],
            'occurrences' => $this['occurrences'],
        ];
    }
}
