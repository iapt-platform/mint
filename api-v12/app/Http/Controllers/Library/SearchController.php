<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Services\OpenSearchService;
use App\DTO\Search\SearchDataDTO;
use Carbon\Carbon;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query    = trim($request->input('q', ''));
        $category = $request->input('category', 'all');
        $page     = max(1, (int) $request->input('page', 1));
        $perPage  = 10;
        $lang = $request->input('language');

        $search = app(OpenSearchService::class);
        // 组装搜索参数
        $params = [
            'query'        => $query,
            'pageSize'     => $perPage,
            'page'     => $page,
            'language'     => $lang,
            'resourceType'     => $request->input('resource_type'),
        ];
        $result = $search->search($params);

        $dto = SearchDataDTO::fromArray($result);
        $results = [];
        foreach ($dto->hits->items as $key => $item) {
            $results[] = [
                'id'     => $item->resId,
                'title'       => $item->title,
                'type'     => $item->type,
                'lang'     => $item->language,
                'category' => $item->type,
                'quality'  => 'featured',
                'snippet'  => !empty($item->highlight) ? $item->highlight : $item->content,
                'updated'  => Carbon::parse($item->updated)->format('Y-m-d'),
            ];
        }

        $aggregations = $dto->aggregations->toArray();
        unset($aggregations['resource_type']);
        // 分页对象（兼容 Blade paginator 风格）
        $pagination = [
            'total'        => $dto->hits->total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int) ceil($dto->hits->total / $perPage)),
        ];

        return view('library.search', [
            'lang'           => $lang,
            'query'          => $query,
            'results'        => $results,
            'pagination'     => $pagination,
            'category'       => 'all',
            'filters'     => $aggregations,
            'types'     => $this->types(),
            'recentUpdates'  => [],
        ]);
    }
    // ── Helpers ──────────────────────────────────────────────────

    private function types(): array
    {
        return [
            ['slug' => 'term',     'label' => '百科词条'],
            ['slug' => 'tipitaka',   'label' => '巴利三藏'],
            ['slug' => 'article',   'label' => '文章文集'],
            ['slug' => 'course', 'label' => '课程'],
            ['slug' => 'dictionary',    'label' => '字典'],
        ];
    }
}
