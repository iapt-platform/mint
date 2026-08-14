<?php

namespace App\Http\Controllers;

use App\DTO\Search\HitItemDTO;
use App\Services\OpenSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SearchPlusController extends Controller
{
    /**
     * 构造函数，注入 OpenSearchService
     */
    public function __construct(protected OpenSearchService $searchService) {}

    /**
     * Display a listing of the resource.
     *
     * 处理搜索请求，支持 fuzzy / exact / semantic / hybrid 四种模式。
     * 接收查询参数并调用 OpenSearchService 执行搜索。
     *
     * 支持 GET 和 POST 请求
     *
     * @param  Request  $request
     *                            - q (string): 搜索关键词
     *                            - resource_type (string): 资源类型 (article|term|dictionary|translation|origin_text|nissaya)
     *                            - granularity (string): 文档颗粒度 (book|chapter|sutta|section|paragraph|sentence)
     *                            - language (string): 语言，如 pali, zh-Hans, zh-Hant, en-US, my
     *                            - category (string): 文档分类 (pali|commentary|subcommentary)
     *                            - tags (array): 标签过滤
     *                            - page_refs (array): 页码标记 ["V3.81","M3.58"]
     *                            - related_id (array): 关联 ID，如 ["chapter_93-5","m.n. 38"]
     *                            - author (string): 作者或译者 (metadata.author)
     *                            - channel (string): 来源渠道 (metadata.channel)
     *                            - page (int): 页码，默认 1
     *                            - page_size (int): 每页数量，默认 20，最大 100
     *                            - search_mode (string): fuzzy|exact|semantic|hybrid，默认 fuzzy
     * @return JsonResponse
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
     * Store a newly created resource in storage.
     *
     * POST 方式调用搜索接口（与 index 方法功能相同）
     *
     * @route POST /api/search
     *
     * @param JSON: 搜索参数（与 index 方法参数相同）
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        // 直接调用 index 方法，复用搜索逻辑
        return $this->index($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
        try {
            return $this->ok(HitItemDTO::fromArray($this->searchService->get($id)));
        } catch (\Throwable $th) {
            return $this->error("no such index {$id}", 404, 404);
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
