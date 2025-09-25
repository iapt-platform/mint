<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\OpenSearchService;


/**
 * 返回示例
 * 请求：GET /api/v2/suggest?q=慈&type=term&limit=5
 * 返回：
 * {
  "success": true,
  "data": {
    "suggestions": [
      {
        "text": "慈悲",
        "resource_type": "translation",
        "language": "zh-Hans"
      },
      {
        "text": "mettā",
        "resource_type": "origin_text",
        "language": "pali"
      },
      {
        "text": "compassion",
        "resource_type": "translation",
        "language": "en-US"
      }
    ]
  }
}

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
     * Display a listing of suggestions.
     *
     * 自动建议接口，基于 OpenSearch completion suggester。
     * 支持术语、巴利罗马化拼写、页码等建议。
     *
     * @param  \Illuminate\Http\Request  $request
     *   - q (string): 输入的部分文本（必填）
     *   - type (string): 建议类型，可选值 term|pali_romanized|page_ref，默认 term
     *   - language (string): 语言过滤，可选
     *   - limit (int): 返回数量，默认 10，最大 50
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query    = $request->input('q', '');
        $type     = $request->input('type', 'term');
        $language = $request->input('language');
        $limit    = min(50, (int) $request->input('limit', 10));

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'error'   => '缺少参数 q'
            ], 400);
        }

        try {
            $rawSuggestions = $this->searchService->suggest($query, $type, $language, $limit);

            // 格式化返回结果：包含 text + resource_type + language
            $suggestions = collect($rawSuggestions)->map(function ($item) {
                return [
                    'text'          => $item['text'] ?? '',
                    'resource_type' => $item['resource_type'] ?? null,
                    'language'      => $item['language'] ?? null,
                ];
            })->all();

            return response()->json([
                'success' => true,
                'data'    => [
                    'suggestions' => $suggestions
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
