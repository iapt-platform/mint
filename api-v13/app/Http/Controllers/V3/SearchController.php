<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexSearchRequest;
use App\Http\Resources\V3\SearchHitResource;
use App\Services\V3\SearchService;
use Illuminate\Http\Request;

/**
 * 全文检索（OpenSearch）
 *
 * 曾叫 `SearchPlusController`，2026-11-19 按 v3 规范重写：FormRequest 校验入参、
 * 响应改成 `{data, meta}`（不再透出 OpenSearch 原始响应）、失败走 Problem Details。
 * 与 v2 的 `SearchController`（`/v2/search`，PostgreSQL 全文检索）是两套实现，互不替代。
 */
class SearchController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly SearchService $search) {}

    /**
     * 全文检索
     *
     * 底层走 OpenSearch。同样的参数也可以用 `POST /v3/search` 放在请求体里提交。
     * 翻页深度受 OpenSearch 限制：`page × per_page` 不能超过 10000，超出 422。
     * 搜索服务连不上或超时回 503（`search-unavailable`）。
     *
     * @unauthenticated
     *
     * @queryParam q string required 检索词
     * @queryParam search_mode string 检索模式：fuzzy 多词模糊（默认）、exact 词形精确、semantic 语义向量、hybrid 模糊 + 语义、definition 黑体定义优先。Enum: fuzzy,exact,semantic,hybrid,definition Default: fuzzy
     * @queryParam resource_type string 资源类型：tipitaka 三藏（段落与整章）、original_text 原文分段、term 术语。Enum: tipitaka,original_text,term
     * @queryParam resource_id string 限定某个资源 id
     * @queryParam granularity string 文档颗粒度。Enum: book,chapter,session,paragraph
     * @queryParam language string 文档语言，如 pi、zh、en
     * @queryParam category string 分类，逗号分隔，须全部命中。Example: sutta,mūla
     * @queryParam tags string 标签，逗号分隔，命中任一
     * @queryParam page_refs array 页码标记，命中任一。Example: ["V3.81","M3.58"]
     * @queryParam related_id array 关联 id（经文段落是 `{book}-{para}`），命中任一。Example: ["93-5"]
     * @queryParam author string 作者或译者
     * @queryParam channel string 来源 channel 的 uuid
     * @queryParam facets boolean 是否在 `meta.facets` 里返回按 resource_type / language / category / granularity 的分面计数。Default: false
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页条数，上限 100。Default: 20
     *
     * @responseStatus 503 搜索服务暂不可用
     *
     * @meta current_page integer 当前页
     * @meta per_page integer 每页条数
     * @meta total integer 命中总数（total_relation 为 gte 时是下限）
     * @meta total_relation string eq 精确，gte 表示「至少这么多」（OpenSearch 只精确计数到 10000）
     * @meta last_page integer 最后一页（受翻页深度限制）
     * @meta facets object 分面计数，只在 facets=true 时出现
     */
    public function index(IndexSearchRequest $request)
    {
        $result = $this->search->search($request->validated());

        return SearchHitResource::collection($result['items'])->additional(['meta' => $result['meta']]);
    }

    /**
     * 全文检索（POST）
     *
     * 与 `GET /v3/search` 完全等价，参数放在 JSON 请求体里（数组参数多、URL 放不下时用）。
     *
     * @unauthenticated
     *
     * @bodyParam q string required 检索词
     * @bodyParam search_mode string 检索模式。Enum: fuzzy,exact,semantic,hybrid,definition Default: fuzzy
     * @bodyParam resource_type string 资源类型。Enum: tipitaka,original_text,term
     * @bodyParam resource_id string 限定某个资源 id
     * @bodyParam granularity string 文档颗粒度。Enum: book,chapter,session,paragraph
     * @bodyParam language string 文档语言
     * @bodyParam category string 分类，逗号分隔，须全部命中
     * @bodyParam tags string 标签，逗号分隔，命中任一
     * @bodyParam page_refs array 页码标记，命中任一
     * @bodyParam related_id array 关联 id，命中任一
     * @bodyParam author string 作者或译者
     * @bodyParam channel string 来源 channel 的 uuid
     * @bodyParam facets boolean 是否返回分面计数。Default: false
     * @bodyParam page integer 页码。Default: 1
     * @bodyParam per_page integer 每页条数，上限 100。Default: 20
     *
     * @responseStatus 503 搜索服务暂不可用
     *
     * @meta current_page integer 当前页
     * @meta per_page integer 每页条数
     * @meta total integer 命中总数（total_relation 为 gte 时是下限）
     * @meta total_relation string eq 精确，gte 表示「至少这么多」
     * @meta last_page integer 最后一页（受翻页深度限制）
     * @meta facets object 分面计数，只在 facets=true 时出现
     */
    public function store(IndexSearchRequest $request)
    {
        $result = $this->search->search($request->validated());

        return SearchHitResource::collection($result['items'])->additional(['meta' => $result['meta']]);
    }

    /**
     * 按 id 取单条检索文档
     *
     * 直接从 OpenSearch 按文档 id 取回，带阅读用的 `display` HTML。
     * 整章正文文档（`tipitaka_chapter_{book}-{para}_{channel}`）按它所属 channel 判权限：
     * 私有 channel 只有拥有者、团队成员、被共享的人能读（要带 token）。
     *
     * @unauthenticated
     *
     * @urlParam search string required OpenSearch 文档 id
     *
     * @responseStatus 403 整章正文所属的 channel 是私有的，且当前身份看不见
     * @responseStatus 503 搜索服务暂不可用
     */
    public function show(Request $request, string $search): SearchHitResource
    {
        return SearchHitResource::make($this->search->find($search, $this->currentUserUid($request)));
    }
}
