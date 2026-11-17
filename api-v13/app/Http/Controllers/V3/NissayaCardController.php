<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ShowNissayaCardRequest;
use App\Http\Resources\V3\NissayaCardResource;
use App\Services\V3\NissayaCardService;

/**
 * 缅文 nissaya 词尾卡片（只读、公开）。
 *
 * 取代 `GET /v2/nissaya-card/{ending}`（`NissayaCardController@show`）。v2 那份在 dashboard-v4 下线前原样保留，不要改动。
 */
class NissayaCardController extends Controller
{
    public function __construct(private readonly NissayaCardService $cards) {}

    /**
     * 缅文词尾卡片
     *
     * 词尾对应的语法术语（说明是新渲染器的节点树），以及它表达的语法关系：关系名与格位都带本地译名，
     * 另有含义分类与翻译建议。术语取 `_System_Grammar_Term_{lang}_`，没有就用 en（`lang` 字段是实际用到的语言）。
     * 词尾查不到时 `term` 为 null、`relations` 为空，不是 404。
     *
     * @unauthenticated
     *
     * @urlParam ending string required 缅文词尾，如 သည်
     *
     * @queryParam lang string 语言，如 zh-Hans。缺省用界面语言（Accept-Language 协商的结果）
     */
    public function __invoke(ShowNissayaCardRequest $request, string $ending)
    {
        return NissayaCardResource::make($this->cards->card($ending, $request->validated('lang') ?? app()->getLocale()));
    }
}
