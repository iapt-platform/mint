<?php

namespace App\Http\Controllers;

use App\Services\ResourceService;
use App\Services\OpenSearchService;
use Illuminate\Http\Request;

class SearchPlusController extends Controller
{
    protected $searchService;

    /**
     * 构造函数，注入 OpenSearchService
     *
     * @param  \App\Services\OpenSearchService  $searchService
     */
    public function __construct(OpenSearchService $searchService)
    {
        $this->searchService = $searchService;
    }
    /**
     * Display a listing of the resource.
     *
     * 处理搜索请求，支持 fuzzy / exact / semantic / hybrid 四种模式。
     * 接收查询参数并调用 OpenSearchService 执行搜索。
     *
     * @param  \Illuminate\Http\Request  $request
     *   - q (string): 搜索关键词
     *   - resource_type (string): 资源类型 (article|term|dictionary|translation|origin_text|nissaya) ✅ 已更新
     *   - granularity (string): 文档颗粒度 (book|chapter|sutta|section|paragraph|sentence) ✅ 已更新
     *   - language (string): 语言，如 pali, zh-Hans, zh-Hant, en-US, my ✅ 已更新
     *   - category (string): 文档分类 (pali|commentary|subcommentary) ✅ 已更新
     *   - tags (array): 标签过滤
     *   - page_refs (array): 页码标记 ["V3.81","M3.58"] ✅ 已更新
     *   - related_id (array): 关联 ID，如 ["chapter_93-5","m.n. 38"] ✅ 新增
     *   - author (string): 作者或译者 (metadata.author) ✅ 新增
     *   - channel (string): 来源渠道 (metadata.channel) ✅ 新增
     *   - page (int): 页码，默认 1
     *   - page_size (int): 每页数量，默认 20，最大 100
     *   - search_mode (string): fuzzy|exact|semantic|hybrid，默认 fuzzy
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 基础参数
        $query        = $request->input('q', '');
        $page         = max(1, (int) $request->input('page', 1));
        $pageSize     = min(100, (int) $request->input('page_size', 20));
        $searchMode   = $request->input('search_mode', 'fuzzy');
        $resourceType = $request->input('resource_type'); // 资源类型
        $granularity  = $request->input('granularity');   // 文档颗粒度
        $language     = $request->input('language');      // 语言
        $category     = $request->input('category');      // 分类
        $tags         = $request->input('tags', []);      // 标签
        $pageRefs     = $request->input('page_refs', []); // 页码标记
        $relatedId    = $request->input('related_id', []); // 关联 ID
        $author       = $request->input('author');        // 作者/译者 (metadata.author)
        $channel      = $request->input('channel');       // 来源渠道 (metadata.channel)

        // 组装搜索参数
        $params = [
            'query'        => $query,
            'page'         => $page,
            'pageSize'     => $pageSize,
            'searchMode'   => $searchMode,
            'resourceType' => $resourceType,
            'granularity'  => $granularity,
            'language'     => $language,
            'category'     => $category,
            'tags'         => $tags,
            'pageRefs'     => $pageRefs,
            'relatedId'    => $relatedId,
            'author'       => $author,
            'channel'      => $channel,
        ];

        try {
            // 调用 OpenSearchService 执行搜索
            $result = $this->searchService->search($params);

            return response()->json([
                'success' => true,
                'data'    => $result,
                'query_info' => [
                    'original_query' => $query,
                    'search_mode'    => $searchMode,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *     * 添加资源
     * @route POST /api/search
     * @param JSON: OpenSearch 格式数据 (e.g., {type, title, content, path_full, ...})
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * 更新资源
     * @route PUT /api/search/{uid}
     * @param JSON: OpenSearch 格式数据
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uid) {}

    /**
     * Remove the specified resource from storage.
     *
     * 删除资源
     * @route DELETE /api/search/{uid}
     * @return \Illuminate\Http\Response
     */
    public function destroy($uid) {}
}
