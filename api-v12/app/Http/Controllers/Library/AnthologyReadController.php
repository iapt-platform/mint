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
    public function read(string $anthologyId, string $articleId)
    {
        // ── 1. 获取文集信息 ───────────────────────────────────────────────────
        $colResult = $this->collectionService->getCollection($anthologyId);
        if (isset($colResult['error'])) {
            abort($colResult['code'] ?? 404, $colResult['error']);
        }

        $col = is_array($colResult['data'])
            ? $colResult['data']
            : $colResult['data']->toArray(request());

        // ── 2. 构建完整目录（所有 level，保留层级信息） ────────────────────
        $fullArticleList = collect($col['article_list'] ?? []);

        // toc：全部节点，交给阅读页左侧目录使用
        // 当前章节标记 active=true、disabled=true（高亮且不可点击）
        $toc = $fullArticleList
            ->values()
            ->map(fn($a) => [
                'id'       => $a['article_id'],
                'title'    => $a['title'],
                'summary'  => '',
                'progress' => 0,
                'level'    => (int) ($a['level'] ?? 1),
                'disabled' => $a['article_id'] === $articleId,
                'active'   => $a['article_id'] === $articleId,
            ])
            ->toArray();

        // ── 3. 获取当前文章内容 ───────────────────────────────────────────────
        $artResult = $this->articleService->getArticle($articleId);
        if (isset($artResult['error'])) {
            abort($artResult['code'] ?? 404, $artResult['error']);
        }

        // ArticleResource 需要 format=html
        $fakeRequest = Request::create('', 'GET', ['format' => 'html']);
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
        // 只在 level=1 的节点间翻页（与目录一致）
        $level1 = $fullArticleList
            ->filter(fn($a) => ($a['level'] ?? 1) === 1)
            ->values();

        $currentIndex = $level1->search(
            fn($a) => $a['article_id'] === $articleId
        );

        $prev = null;
        $next = null;

        if ($currentIndex !== false) {
            if ($currentIndex > 0) {
                $prevItem = $level1[$currentIndex - 1];
                $prev = [
                    'id'    => $prevItem['article_id'],
                    'title' => $prevItem['title'],
                ];
            }
            if ($currentIndex < $level1->count() - 1) {
                $nextItem = $level1[$currentIndex + 1];
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

        // blade 里有 $relatedBooks，传空数组防止 undefined variable
        $relatedBooks = [];

        // 翻页路由需要 anthologyId，传给 blade 供覆盖路由使用
        return view('library.book.read', compact('book', 'relatedBooks', 'anthologyId'));
    }
}
