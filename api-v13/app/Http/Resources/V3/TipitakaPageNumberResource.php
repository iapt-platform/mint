<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一处印本页码（或 CS 段落号）在三藏里的位置。
 *
 * `edition`：PTS / My（缅甸第六次结集版）/ VRI / Thai / Other / CS（CS 版印的段落号）。
 * `volume` 为 null 表示卷号未知（库里记作 0）或不分卷（CS）。`book` / `para` 是页码标记所在段落；
 * `book_id` 是 `pcd_book_id`，`book_title` 是书名，`book_para` 是书名所在段（CS 段落号按书编号，定位要用它）。
 * `edition_title` 是该版本的书名（静态书名表，PTS / 缅 / VRI 各有一套，其余用通用名；CS 为 null），
 * v6 的印本页阅读页按「版本_版本书名_卷_页」定位。`tags` 是书的 tag（同 tipitaka-word-paragraphs/aggregate），客户端按它做目录过滤。
 */
class TipitakaPageNumberResource extends BaseResource
{
    /**
     * @return array{
     *     edition: string,
     *     volume: int|null,
     *     page: int,
     *     book: int,
     *     para: int,
     *     book_id: int,
     *     book_title: string,
     *     book_para: int,
     *     edition_title: string|null,
     *     layer: string|null,
     *     tags: list<string>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'edition' => $this['edition'],
            'volume' => $this['volume'],
            'page' => $this['page'],
            'book' => $this['book'],
            'para' => $this['para'],
            'book_id' => $this['book_id'],
            'book_title' => $this['book_title'],
            'book_para' => $this['book_para'],
            'edition_title' => $this['edition_title'],
            'layer' => $this['layer'],
            'tags' => $this['tags'],
        ];
    }
}
