<?php

namespace App\Http\Resources\V3;

use App\Services\V3\TipitakaChapterService;
use Illuminate\Http\Request;

/**
 * 三藏里的一个章节标题。
 *
 * 载荷是 {@see TipitakaChapterService::search()} 组好的数组，不查库。底层是 pali_texts 中
 * level < 8 的一行；译名来自公开 channel 的 progress_chapters.title。
 *
 * 类型核对自 information_schema：pali_texts.book / paragraph / level / chapter_len /
 * chapter_strlen / parent / prev_chapter / next_chapter 都是 integer，toc 是 text，path 是 json。
 */
class TipitakaChapterResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     book: int,
     *     para: int,
     *     level: int,
     *     title: string,
     *     translations: array<int, array{lang: string, title: string}>,
     *     layer: string,
     *     paragraphs: int,
     *     chars: int,
     *     parent: int|null,
     *     prev: int|null,
     *     next: int|null,
     *     path: array<int, array{book: int, para: int, title: string, level: int}>,
     *     score?: float
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
