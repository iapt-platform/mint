<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\OpenSearchService;
use App\DTO\Search\SearchDataDTO;


class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query    = trim($request->input('q', ''));
        $category = $request->input('category', 'all');
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = 10;
        $lang = $request->input('lang');

        $search = app(OpenSearchService::class);
        // 组装搜索参数
        $params = [
            'query'        => $query,
            'pageSize'     => $perPage,
            'page'     => $page,
            'language'     => $lang,
            'resourceType'     => $request->input('type'),
        ];
        $result = $search->search($params);

        $dto = SearchDataDTO::fromArray($result);
        $results = [];
        foreach ($dto->hits->items as $key => $item) {
            $results[] = [
                'word'     => 'word',
                'zh'       => $item->title,
                'lang'     => 'pi',
                'category' => '法义术语',
                'quality'  => 'featured',
                'snippet'  => $item->highlight,
                'updated'  => '2025-11-12',
            ];
        }

        $category = $dto->aggregations->category->buckets;

        // 分页对象（兼容 Blade paginator 风格）
        $pagination = [
            'total'        => $dto->hits->total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($dto->hits->total / $perPage)),
        ];

        return view('wiki.search', [
            'lang'           => $lang,
            'query'          => $query,
            'results'        => $results,
            'pagination'     => $pagination,
            'category'       => 'all',
            'filters'     => $category,
            'categories'     => $this->types(),
            'recentUpdates'  => [],
        ]);
    }
    // ── Helpers ──────────────────────────────────────────────────

    private function types(): array
    {
        return [
            ['slug' => 'all',      'label' => '全部'],
            ['slug' => 'term',     'label' => '法义术语'],
            ['slug' => 'original_text',   'label' => '原文'],
            ['slug' => 'translation',     'label' => '译文'],
            ['slug' => 'article',   'label' => '文章'],
            ['slug' => 'course', 'label' => '课程'],
            ['slug' => 'dictionary',    'label' => '字典'],
        ];
    }
}
