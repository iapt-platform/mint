<?php

namespace App\Http\Controllers;

use App\DTO\Search\HitItemDTO;
use App\Http\Resources\V3Resource;
use App\Services\OpenSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchPlusController extends Controller
{
    /**
     * 构造函数，注入 OpenSearchService
     */
    public function __construct(protected OpenSearchService $searchService) {}

    /**
     * 全文检索
     *
     * 支持 fuzzy / exact / semantic / hybrid 四种检索模式，底层走 OpenSearch。
     * 同样的参数也可以用 POST /v3/search 提交（参数多、URL 放不下时用）。
     *
     * @unauthenticated
     *
     * @queryParam q string required 搜索关键词
     * @queryParam search_mode string 检索模式。Enum: fuzzy,exact,semantic,hybrid Default: fuzzy
     * @queryParam resource_type string 资源类型。
     *             Enum: article,term,dictionary,translation,origin_text,nissaya
     * @queryParam resource_id string 限定某个资源 id
     * @queryParam granularity string 文档颗粒度。
     *             Enum: book,chapter,sutta,section,paragraph,sentence
     * @queryParam language string 语言。Example: zh-Hans
     * @queryParam category string 文档分类，逗号分隔。Enum: pali,commentary,subcommentary
     * @queryParam tags string 标签过滤，逗号分隔
     * @queryParam page_refs array 页码标记。Example: ["V3.81","M3.58"]
     * @queryParam related_id array 关联 id。Example: ["chapter_93-5","m.n. 38"]
     * @queryParam author string 按作者或译者过滤
     * @queryParam channel string 按来源 channel 过滤
     * @queryParam page integer 页码。Default: 1
     * @queryParam page_size integer 每页数量，上限 100。Default: 20
     */
    public function index(Request $request)
    {
        // 获取所有输入参数（自动兼容 GET 和 POST）
        $input = $request->all();

        // 基础参数 - 使用 $input 或直接用 $request->input() (已兼容 GET/POST)
        $query = $request->input('q', '');
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = min(100, (int) $request->input('page_size', 20));
        $searchMode = $request->input('search_mode', 'fuzzy');
        $resourceType = $request->input('resource_type'); // 资源类型
        $resourceId = $request->input('resource_id'); // 资源类型
        $granularity = $request->input('granularity');   // 文档颗粒度
        $language = $request->input('language');      // 语言
        $category = $request->has('category') ? explode(',', $request->input('category')) : null;      // 分类
        $tags = $request->has('tags') ? explode(',', $request->input('tags')) : [];      // 标签
        $pageRefs = $request->input('page_refs', []); // 页码标记
        $relatedId = $request->input('related_id'); // 关联 ID
        $author = $request->input('author');        // 作者/译者 (metadata.author)
        $channel = $request->input('channel');       // 来源渠道 (metadata.channel)

        // 确保数组类型参数正确解析（POST 时可能是 JSON 字符串）
        $tags = is_array($tags) ? $tags : (is_string($tags) ? json_decode($tags, true) ?? [] : []);
        $pageRefs = is_array($pageRefs) ? $pageRefs : (is_string($pageRefs) ? json_decode($pageRefs, true) ?? [] : []);

        // 组装搜索参数
        $params = [
            'query' => $query,
            'page' => $page,
            'pageSize' => $pageSize,
            'searchMode' => $searchMode,
            'resourceType' => $resourceType,
            'resourceId' => $resourceId,
            'granularity' => $granularity,
            'language' => $language,
            'category' => $category,
            'tags' => $tags,
            'pageRefs' => $pageRefs,
            'relatedId' => $relatedId,
            'author' => $author,
            'channel' => $channel,
        ];

        try {
            // 调用 OpenSearchService 执行搜索
            $result = $this->searchService->search($params);

            return response()->json([
                'success' => true,
                'data' => $result,
                'query_info' => [
                    'original_query' => $query,
                    'search_mode' => $searchMode,
                    'request_method' => $request->method(), // 可选：返回请求方法
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 全文检索（POST）
     *
     * 与 `GET /v3/search` 完全等价，只是把参数放在请求体里——参数多、
     * 尤其是 page_refs / related_id 这类数组时，URL 放不下。
     *
     * @unauthenticated
     *
     * @bodyParam q string required 搜索关键词
     * @bodyParam search_mode string 检索模式。Enum: fuzzy,exact,semantic,hybrid Default: fuzzy
     * @bodyParam resource_type string 资源类型
     * @bodyParam granularity string 文档颗粒度
     * @bodyParam language string 语言
     * @bodyParam category string 文档分类，逗号分隔
     * @bodyParam tags string 标签，逗号分隔
     * @bodyParam page_refs array 页码标记
     * @bodyParam related_id array 关联 id
     * @bodyParam author string 作者或译者
     * @bodyParam channel string 来源 channel
     * @bodyParam page integer 页码。Default: 1
     * @bodyParam page_size integer 每页数量，上限 100。Default: 20
     */
    public function store(Request $request)
    {
        // 直接调用 index 方法，复用搜索逻辑
        return $this->index($request);
    }

    /**
     * 按 id 取单条检索结果
     *
     * 直接从 OpenSearch 按文档 id 取回，不走检索。取不到返回 404。
     *
     * @unauthenticated
     *
     * @urlParam search string required OpenSearch 文档 id
     */
    public function show($id)
    {
        //
        try {
            return V3Resource::make(HitItemDTO::fromArray($this->searchService->get($id)));
        } catch (\Throwable $th) {
            abort(404, __('site.not_found'));
        }
    }

    /**
     * 更新资源
     *
     * @route PUT /api/search/{uid}
     *
     * @param JSON: OpenSearch 格式数据
     * @return Response
     */
    public function update(Request $request, $uid) {}

    /**
     * Remove the specified resource from storage.
     *
     * 删除资源
     *
     * @route DELETE /api/search/{uid}
     *
     * @return Response
     */
    public function destroy($uid) {}
}
