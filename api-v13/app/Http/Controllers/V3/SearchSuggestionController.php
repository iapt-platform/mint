<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexSearchSuggestionRequest;
use App\Http\Resources\V3\SearchSuggestionResource;
use App\Services\V3\SearchService;

/**
 * 搜索建议（搜索框的前缀补全，OpenSearch completion suggester）
 *
 * 取代早期的 `GET /v3/search-suggest`（`V3\SearchSuggestController`，2026-11-19 删除）：
 * 路径复数化；`{success, data:{query, suggestions, total}}` 改成 `{data, meta}`；空 q 从 400 改 422；
 * `limit` 从「每个字段各取」改成总条数并去重；去掉从未生效的 `language` 参数。
 * 无对应的 v2 端点。
 */
class SearchSuggestionController extends Controller
{
    public function __construct(private readonly SearchService $search) {}

    /**
     * 搜索建议
     *
     * 按已输入的前缀给出补全候选，各字段合并后按分数排序、同一个词只出现一次。
     * 搜索服务连不上或超时回 503（`search-unavailable`）。
     *
     * @unauthenticated
     *
     * @queryParam q string required 已输入的前缀
     * @queryParam fields string 在哪些字段上补全，逗号分隔，可选 title_pali、title_zh、content_pali、content_zh；不传则全部。Example: title_pali,content_pali
     * @queryParam limit integer 最多返回几条，上限 50。Default: 10
     *
     * @responseStatus 503 搜索服务暂不可用
     *
     * @meta total integer 本次返回的条数
     */
    public function __invoke(IndexSearchSuggestionRequest $request)
    {
        $items = $this->search->suggest($request->validated());

        return SearchSuggestionResource::collection($items)->additional(['meta' => ['total' => count($items)]]);
    }
}
