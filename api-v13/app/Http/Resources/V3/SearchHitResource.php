<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一条检索命中（OpenSearch 文档归一化后的结构，见 `App\Services\V3\SearchService::hit()`）。
 *
 * `related_id` 对经文段落是 `{book}-{para}` 坐标；`display` 只在按 id 取单条时才有（阅读用的 HTML）。
 */
class SearchHitResource extends BaseResource
{
    /**
     * @return array{
     *     id: string,
     *     resource_type: string,
     *     resource_id: string|null,
     *     granularity: string|null,
     *     language: string|null,
     *     related_id: string|null,
     *     score: float|null,
     *     title: string,
     *     content: string,
     *     highlight: string|null,
     *     bold: list<string>,
     *     path: string|null,
     *     category: list<string>,
     *     tags: list<string>,
     *     updated_at: string|null,
     *     display?: string
     * }
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
