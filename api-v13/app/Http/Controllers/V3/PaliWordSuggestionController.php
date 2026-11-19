<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexPaliWordSuggestionRequest;
use App\Http\Resources\V3\PaliWordSuggestionResource;
use App\Services\V3\PaliWordSuggestionService;

/**
 * 巴利词补全（只读、公开）。
 *
 * 取代 `GET /v2/pali-word-index?view=key`（`WordIndexController@index`）。v2 那份在 dashboard-v4
 * 下线前原样保留，不要改动。
 */
class PaliWordSuggestionController extends Controller
{
    public function __construct(private readonly PaliWordSuggestionService $words) {}

    /**
     * 巴利词补全
     *
     * 按已输入的前缀列出三藏里出现过的词形，带出现次数与黑体次数。前缀同时匹配去掉变音符号的写法
     * （输入 dhamma 也能补出 dhammā），不区分大小写。与输入完全相同的词排在最前，其余按出现次数降序。
     *
     * @unauthenticated
     *
     * @queryParam q string required 已输入的前缀。Example: vitakk
     * @queryParam limit integer 最多返回几条，上限 50。Default: 10
     *
     * @meta total integer 本次返回的条数
     */
    public function __invoke(IndexPaliWordSuggestionRequest $request)
    {
        $items = $this->words->suggest($request->validated('q'), $request->integer('limit', 10));

        return PaliWordSuggestionResource::collection($items)->additional(['meta' => ['total' => count($items)]]);
    }
}
