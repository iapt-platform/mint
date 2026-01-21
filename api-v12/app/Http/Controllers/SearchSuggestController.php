<?php
// api-v8/app/Http/Controllers/SearchSuggestController.php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OpenSearchService;

/**
 * 搜索自动建议控制器
 *
 * 返回示例：
 *
 * 请求：GET /api/v2/suggest?q=dhamma&fields=title,content&limit=10
 *
 * 返回：
 * {
 *   "success": true,
 *   "data": {
 *     "query": "dhamma",
 *     "suggestions": [
 *       {
 *         "text": "dhammapada",
 *         "source": "title",
 *         "score": 5.2,
 *         "resource_type": "sutta",
 *         "language": "pali",
 *         "doc_id": "doc_123"
 *       },
 *       {
 *         "text": "dhammapadā",
 *         "source": "content",
 *         "score": 4.8,
 *         "resource_type": "commentary",
 *         "language": "pali",
 *         "doc_id": "doc_456"
 *       }
 *     ],
 *     "total": 2
 *   }
 * }
 */
class SearchSuggestController extends Controller
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
     * # 1. 查询所有字段（默认）
GET /api/v2/suggest?q=dhamma&limit=10

# 2. 只查询标题
GET /api/v2/suggest?q=dhamma&fields=title&limit=10

# 3. 查询标题和内容
GET /api/v2/suggest?q=dhamma&fields=title,content&limit=10

# 4. 查询页面引用，带语言过滤
GET /api/v2/suggest?q=M.1&fields=page_refs&language=pali&limit=5

# 5. 数组形式传递多个字段
GET /api/v2/suggest?q=dhamma&fields[]=title&fields[]=content&limit=10
     */
    /**
     * 自动建议接口
     *
     * 基于 OpenSearch completion suggester，支持从不同字段获取建议。
     *
     * @param  \Illuminate\Http\Request  $request
     *   - q (string): 输入的部分文本（必填）
     *   - fields (string|array): 要查询的字段，可选值：
     *       - 不传：查询所有字段 (title, content, page_refs)
     *       - 单个字段：'title' | 'content' | 'page_refs'
     *       - 多个字段：'title,content' 或 ['title', 'content']
     *   - language (string): 语言过滤，可选（如：pali, zh, en）
     *   - limit (int): 每个字段返回的建议数量，默认 10，最大 50
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // 验证必填参数
        $query = $request->input('q', '');
        if (empty($query)) {
            return response()->json([
                'success' => false,
                'error'   => '缺少参数 q（查询文本）'
            ], 400);
        }

        // 解析 fields 参数
        $fields = $this->parseFields($request->input('fields'));

        // 获取其他参数
        $language = $request->input('language', null);
        $limit = min(50, max(1, (int) $request->input('limit', 10)));

        try {
            // 调用搜索服务
            $rawSuggestions = $this->searchService->suggest(
                $query,
                $fields,
                $language,
                $limit
            );

            // 格式化返回结果
            $suggestions = $this->formatSuggestions($rawSuggestions);

            return response()->json([
                'success' => true,
                'data'    => [
                    'query'       => $query,
                    'suggestions' => $suggestions,
                    'total'       => count($suggestions)
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error'   => '无效的字段参数：' . $e->getMessage(),
                'hint'    => '有效的字段值：title, content, page_refs'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => '搜索建议失败：' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 解析 fields 参数
     *
     * @param  mixed  $fields
     * @return string|array|null
     */
    protected function parseFields($fields)
    {
        if ($fields === null) {
            return null;  // 查询所有字段
        }

        if (is_string($fields)) {
            // 如果是逗号分隔的字符串，转换为数组
            if (strpos($fields, ',') !== false) {
                $fieldsArray = array_map('trim', explode(',', $fields));
                return $fieldsArray;
            }
            // 单个字段
            return $fields;
        }

        if (is_array($fields)) {
            return $fields;
        }

        return null;
    }

    /**
     * 格式化建议结果
     *
     * @param  array  $rawSuggestions
     * @return array
     */
    protected function formatSuggestions(array $rawSuggestions): array
    {
        return collect($rawSuggestions)->map(function ($item) {
            $docSource = $item['doc_source'] ?? [];

            return [
                'text'          => $item['text'] ?? '',
                'source'        => $item['source'] ?? null,
                'score'         => round($item['score'] ?? 0, 2),
                'resource_type' => $docSource['resource_type'] ?? null,
                'language'      => $docSource['language'] ?? null,
                'doc_id'        => $item['doc_id'] ?? null,
                // 可选：添加更多元数据
                'category'      => $docSource['category'] ?? null,
                'granularity'   => $docSource['granularity'] ?? null,
            ];
        })->all();
    }
}
