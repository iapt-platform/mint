<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 一条搜索建议（OpenSearch completion 候选，见 `App\Services\V3\SearchService::suggest()`）。
 *
 * `source` 是候选来自哪个补全字段；`doc_id` 是候选所在的检索文档，可直接 `GET /v3/search/{doc_id}`。
 */
class SearchSuggestionResource extends BaseResource
{
    /**
     * @return array{
     *     text: string,
     *     source: string,
     *     score: float,
     *     doc_id: string|null,
     *     resource_type: string|null,
     *     language: string|null,
     *     granularity: string|null,
     *     category: list<string>
     * }
     */
    public function toArray(Request $request): array
    {
        return $this->resource;
    }
}
