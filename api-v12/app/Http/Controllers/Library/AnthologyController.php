<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Services\CollectionService;
use Illuminate\Http\Request;

class AnthologyController extends Controller
{
    // 封面渐变色池：uid 首字节取余循环，保证同一文集颜色稳定
    private array $coverGradients = [
        'linear-gradient(160deg, #2d2010, #4a3010)',
        'linear-gradient(160deg, #1a2d10, #2d4a18)',
        'linear-gradient(160deg, #0d1f3c, #1a3660)',
        'linear-gradient(160deg, #2d1020, #4a1830)',
        'linear-gradient(160deg, #1a1a2d, #2a2a50)',
        'linear-gradient(160deg, #1a2820, #2d4438)',
    ];

    // 作者色池：同上，根据 studio.id 首字节取余
    private array $authorColors = [
        '#c8860a',
        '#2e7d32',
        '#1565c0',
        '#6a1b9a',
        '#c62828',
        '#00695c',
        '#4e342e',
        '#37474f',
    ];

    public function __construct(private CollectionService $collectionService) {}

    // -------------------------------------------------------------------------
    // 从 uid / id 字符串中提取一个稳定的整数，用于色池取余
    // -------------------------------------------------------------------------
    private function colorIndex(string $uid): int
    {
        return hexdec(substr(str_replace('-', '', $uid), 0, 4)) % 255;
    }

    // -------------------------------------------------------------------------
    // 将 studio 对象转换为 blade 所需的 author 数组
    // -------------------------------------------------------------------------
    private function formatAuthor(array $studio): array
    {
        $name     = $studio['nickName'] ?? $studio['studioName'] ?? '未知';
        $initials = mb_substr($name, 0, 2);
        $colorIdx = $this->colorIndex($studio['id'] ?? '0');

        return [
            'name'     => $name,
            'initials' => $initials,
            'color'    => $this->authorColors[$colorIdx % count($this->authorColors)],
            'avatar'   => $studio['avatar'] ?? null,
        ];
    }

    // -------------------------------------------------------------------------
    // 将 CollectionResource 单条转换为 index 卡片所需格式
    // -------------------------------------------------------------------------
    private function formatForCard(array $item, int $index): array
    {
        $uid      = $item['uid'];
        $colorIdx = $this->colorIndex($uid);

        // article_list => 章节标题列表
        $chapters = collect($item['article_list'] ?? [])
            ->pluck('title')
            ->toArray();

        return [
            'id'             => $uid,
            'title'          => $item['title'],
            'subtitle'       => $item['subtitle'] ?? null,
            'description'    => $item['summary'] ?? null,
            'cover_image'    => null, // 暂无封面图字段，留空走渐变
            'cover_gradient' => $this->coverGradients[$colorIdx % count($this->coverGradients)],
            'author'         => $this->formatAuthor($item['studio'] ?? []),
            'chapters'       => $chapters,
            'children_number' => $item['childrenNumber'] ?? count($chapters),
            'updated_at'     => isset($item['updated_at'])
                ? \Carbon\Carbon::parse($item['updated_at'])->format('Y-m-d')
                : '',
        ];
    }

    // =========================================================================
    // index
    // =========================================================================
    public function index(Request $request)
    {
        $perPage     = 10;
        $currentPage = (int) $request->input('page', 1);

        $result = $this->collectionService->getPublicList($perPage, $currentPage);

        // $result['data'] 是 CollectionResource collection，转为数组逐条加工
        $items = collect($result['data'])
            ->values()
            ->map(fn($item, $i) => $this->formatForCard(
                is_array($item) ? $item : $item->toArray(request()),
                $i
            ));

        $total = $result['total'];

        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // 侧边栏作者列表：从当页数据聚合（如需完整列表可单独查询）
        $authors = $items
            ->groupBy(fn($i) => $i['author']['name'])
            ->map(fn($group, $name) => [
                'name'     => $name,
                'initials' => $group->first()['author']['initials'],
                'color'    => $group->first()['author']['color'],
                'avatar'   => $group->first()['author']['avatar'],
                'count'    => $group->count(),
            ])
            ->values();

        return view('library.anthology.index', [
            'anthologies' => $paginator,
            'authors'     => $authors,
            'total'       => $total,
        ]);
    }

    // =========================================================================
    // show
    // =========================================================================
    public function show(string $uid)
    {
        $result = $this->collectionService->getCollection($uid);

        if (isset($result['error'])) {
            abort($result['code'] ?? 404, $result['error']);
        }

        $raw = is_array($result['data'])
            ? $result['data']
            : $result['data']->toArray(request());

        $colorIdx = $this->colorIndex($raw['uid']);
        $author   = $this->formatAuthor($raw['studio'] ?? []);

        // 只保留 level=1 的顶级章节
        $articles = collect($raw['article_list'] ?? [])
            ->filter(fn($a) => ($a['level'] ?? 1) === 1)
            ->values()
            ->map(fn($a, $i) => [
                'id'    => $a['article_id'],
                'order' => $i + 1,
                'title' => $a['title'],
            ])
            ->toArray();

        $anthology = [
            'id'             => $raw['uid'],
            'title'          => $raw['title'],
            'subtitle'       => $raw['subtitle'] ?? null,
            'description'    => $raw['summary'] ?? $raw['subtitle'] ?? null,
            'about'          => $raw['summary'] ?? null,
            'cover_image'    => null,
            'cover_gradient' => $this->coverGradients[$colorIdx % count($this->coverGradients)],
            'tags'           => array_filter([$raw['lang'] ?? null]),
            'language'       => $raw['lang'] ?? null,
            'category'       => null,
            'created_at'     => isset($raw['created_at'])
                ? \Carbon\Carbon::parse($raw['created_at'])->format('Y-m-d')
                : '',
            'updated_at'     => isset($raw['updated_at'])
                ? \Carbon\Carbon::parse($raw['updated_at'])->format('Y-m-d')
                : '',
            'children_number' => $raw['childrenNumber'] ?? 0,
            'author'         => array_merge($author, [
                'bio'           => null,
                'article_count' => $raw['childrenNumber'] ?? 0,
            ]),
            'articles'       => $articles,
        ];

        // 相关文集：同作者其他文集
        $relatedResult = $this->collectionService->getPublicList(20, 1);
        $related = collect($relatedResult['data'])
            ->map(fn($item) => is_array($item) ? $item : $item->toArray(request()))
            ->filter(
                fn($item) =>
                $item['uid'] !== $uid &&
                    ($item['studio']['id'] ?? '') === ($raw['studio']['id'] ?? '')
            )
            ->take(3)
            ->map(fn($item) => [
                'id'             => $item['uid'],
                'title'          => $item['title'],
                'author_name'    => $item['studio']['nickName'] ?? $item['studio']['studioName'] ?? '',
                'cover_gradient' => $this->coverGradients[$this->colorIndex($item['uid']) % count($this->coverGradients)],
            ])
            ->values();

        return view('library.anthology.show', [
            'anthology' => $anthology,
            'related'   => $related,
        ]);
    }
}
