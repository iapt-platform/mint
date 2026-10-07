<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一本关联书：`GET /v3/tipitaka-related-paragraphs` 的 `data[]` 元素。
 *
 * 载荷是 Service 已拼好、补全书名/标签/目录路径的普通数组（不是 Eloquent 模型），
 * 本类只做纯映射。字段相对 v2 有改名：`book_title_pali`→`title`、`cs6_para`→`cs_para`。
 */
class RelatedParagraphResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     book: int,
     *     book_id: int,
     *     title: string|null,
     *     cs_para: int,
     *     para: list<int>,
     *     tags: list<string>,
     *     path: list<array{title: string, level: int}>|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'book' => $this->resource['book'],
            'book_id' => $this->resource['book_id'],
            'title' => $this->resource['title'],
            'cs_para' => $this->resource['cs_para'],
            'para' => $this->resource['para'],
            'tags' => $this->resource['tags'],
            'path' => $this->resource['path'],
        ];
    }
}
