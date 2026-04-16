<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Services\CollectionService;
use App\Services\ArticleService;
use Illuminate\Http\Request;

class AnthologyReadController extends Controller
{
    public function __construct(
        private CollectionService $collectionService,
        private ArticleService    $articleService,
    ) {}

    // =========================================================================
    // read
    // GET /library/anthology/{anthology}/read/{article}
    // =========================================================================
    public function read(Request $request, string $anthologyId, string $articleId)
    {
        // ── 1. 获取文集信息 ───────────────────────────────────────────────────
        $colResult = $this->collectionService->getCollection($anthologyId);
        if (isset($colResult['error'])) {
            abort($colResult['code'] ?? 404, $colResult['error']);
        }

        $col = is_array($colResult['data'])
            ? $colResult['data']
            : $colResult['data']->toArray(request());

        // ── 2. 构建目录（方案A：展开祖先链，其余折叠） ───────────────────────
        $fullArticleList = collect($col['article_list'] ?? []);
        $toc = $this->buildCollapsedToc($fullArticleList->toArray(), $articleId);

        // ── 3. 获取当前文章内容 ───────────────────────────────────────────────
        $artResult = $this->articleService->getArticle($articleId);
        if (isset($artResult['error'])) {
            abort($artResult['code'] ?? 404, $artResult['error']);
        }

        // ArticleResource 需要 format=html
        $urlParam = [
            'mode' => 'read',
            'format' => 'html',
            'anthology' => $anthologyId,
            'origin' => 'true',
            'paragraph' => true,
        ];
        if ($request->has('channel')) {
            $urlParam['channel'] = $request->input('channel');
        }
        $fakeRequest = Request::create('', 'GET', $urlParam);
        $artResource = $artResult['data'];
        $artArray    = $artResource->toArray($fakeRequest);

        // content 统一包装成 book.read 期望的格式：
        // [ ['id'=>..., 'level'=>1, 'text'=>[[ html_string ]]], ... ]
        $content = [[
            'id'    => $articleId,
            'level' => 100,
            'text'  => [[$artArray['html'] ?? $artArray['content'] ?? 'null']],
        ]];

        // ── 4. 计算翻页（pagination） ─────────────────────────────────────────

        $currentIndex = $fullArticleList->search(
            fn($a) => $a['article_id'] === $articleId
        );

        $prev = null;
        $next = null;

        if ($currentIndex !== false) {
            if ($currentIndex > 0) {
                $prevItem = $fullArticleList[$currentIndex - 1];
                $prev = [
                    'id'    => $prevItem['article_id'],
                    'title' => $prevItem['title'],
                ];
            }
            if ($currentIndex < $fullArticleList->count() - 1) {
                $nextItem = $fullArticleList[$currentIndex + 1];
                $next = [
                    'id'    => $nextItem['article_id'],
                    'title' => $nextItem['title'],
                ];
            }
        }

        $pagination = [
            'start' => 0,
            'end'   => 0,
            'prev'  => $prev,
            'next'  => $next,
        ];

        // ── 5. 组装 $book（对齐 book.read 的数据结构） ───────────────────────
        $studio = $col['studio'] ?? [];

        // blade 用 $book['publisher']->username / ->nickname，必须是对象
        $publisher = (object) [
            'username' => $studio['studioName'] ?? '',
            'nickname' => $studio['nickName']   ?? $studio['studioName'] ?? '',
        ];

        $book = [
            'id'          => $articleId,
            'title'       => $artArray['title'] ?? ($col['title'] ?? ''),
            'author'      => $studio['nickName'] ?? $studio['studioName'] ?? '',
            'publisher'   => $publisher,
            'type'        => '',
            'category_id' => null,
            'cover'       => null,
            'description' => $col['summary'] ?? '',
            'language'    => $col['lang'] ?? '',
            'anthology'   => [
                'id'    => $anthologyId,
                'title' => $col['title'] ?? '',
            ],
            'categories'  => [],
            'tags'        => [],
            'downloads'   => [],
            'toc'         => $toc,
            'pagination'  => $pagination,
            'content'     => $content,
        ];
        $channels = $this->articleService->articleChannels($articleId);
        // blade 里有 $relatedBooks，传空数组防止 undefined variable
        $relatedBooks = [];
        $editor_link = config('mint.server.dashboard_base_path') . "/workspace/anthology/{$anthologyId}/{$book['id']}";

        // 翻页路由需要 anthologyId，传给 blade 供覆盖路由使用
        return view('library.book.read', compact('book', 'relatedBooks', 'anthologyId', 'channels', 'editor_link'));
    }

    // =========================================================================
    // buildCollapsedToc
    //
    // 规则：
    //   1. 所有 level=1 节点始终显示
    //   2. 找出当前节点的「祖先链」（从 root 到当前节点经过的每个节点）
    //   3. 祖先链上每个节点的「直接子节点」都展开显示
    //   4. 当前节点本身若有子节点，同样展开一级
    //   5. 其余节点隐藏
    // =========================================================================
    private function buildCollapsedToc(array $list, string $currentId): array
    {
        // ── Step 1：为每个节点推导父节点 id ──────────────────────────────────
        // article_list 是有序的，父节点 = 当前节点之前最近的 level 更小的节点
        $parents = [];  // article_id => parent_article_id | null
        $stack   = [];  // 维护祖先栈 [ ['id'=>..., 'level'=>...], ... ]

        foreach ($list as $item) {
            $id    = $item['article_id'];
            $level = (int) ($item['level'] ?? 1);

            // 弹出所有 level >= 当前 level 的栈顶
            while (!empty($stack) && $stack[count($stack) - 1]['level'] >= $level) {
                array_pop($stack);
            }

            $parents[$id] = empty($stack) ? null : $stack[count($stack) - 1]['id'];
            $stack[]      = ['id' => $id, 'level' => $level];
        }

        // ── Step 2：找出当前节点的祖先链 ─────────────────────────────────────
        $ancestorIds = [];
        $cursor      = $currentId;
        while (isset($parents[$cursor]) && $parents[$cursor] !== null) {
            $cursor        = $parents[$cursor];
            $ancestorIds[] = $cursor;
        }
        $ancestorSet = array_flip($ancestorIds);

        // ── Step 3：需要展开子节点的集合 = 祖先链 + 当前节点 ─────────────────
        $expandParentSet              = $ancestorSet;
        $expandParentSet[$currentId]  = true;

        // ── Step 4：过滤构建 toc ──────────────────────────────────────────────
        $toc = [];
        foreach ($list as $item) {
            $id       = $item['article_id'];
            $level    = (int) ($item['level'] ?? 1);
            $parentId = $parents[$id];
            $isActive = $id === $currentId;

            // 显示条件：
            //   a) level=1（始终显示）
            //   b) 父节点在 expandParentSet 中（祖先链或当前节点的直接子节点）
            $visible = $level === 1
                || ($parentId !== null && isset($expandParentSet[$parentId]));

            if (!$visible) {
                continue;
            }

            $toc[] = [
                'id'       => $id,
                'title'    => $item['title'],
                'summary'  => '',
                'progress' => 0,
                'level'    => $level,
                'disabled' => $isActive,
                'active'   => $isActive,
            ];
        }

        return $toc;
    }
}
