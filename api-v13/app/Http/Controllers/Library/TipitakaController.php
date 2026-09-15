<?php

namespace App\Http\Controllers\Library;

use App\Http\Api\ChannelApi;
use App\Http\Controllers\Controller;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Tag;
use App\Models\TagMap;
use App\Services\TermService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TipitakaController extends Controller
{
    // 封面渐变色池：uid 首字节取余循环，保证同一文集颜色稳定
    private array $coverGradients = [
        'linear-gradient(160deg, #2d1020, #ae6b8b)',
        'linear-gradient(160deg, #1a2d10,rgba(75, 114, 36, 0.61))',
        'linear-gradient(160deg, #0d1f3c,rgb(55, 98, 150))',
        'linear-gradient(160deg, #2d1020,rgb(151, 69, 94))',
        'linear-gradient(160deg, #1a1a2d,rgb(76, 68, 146))',
        'linear-gradient(160deg, #1a2820,rgb(55, 124, 99))',
    ];

    /**
     * 构造函数，注入 TermService
     */
    public function __construct(
        protected TermService $termService,
    ) {}

    // -------------------------------------------------------------------------
    // 从 uid / id 字符串中提取一个稳定的整数，用于色池取余
    // -------------------------------------------------------------------------
    private function colorIndex(string $uid): int
    {
        return hexdec(substr(str_replace('-', '', $uid), 0, 4)) % 255;
    }

    protected static int $nextId = 1;

    // app/Http/Controllers/Library/CategoryController.php
    // category() 方法修改版
    // 变更：
    //   1. $id 改为可选参数，无参数时显示顶级分类（首页复用）
    //   2. 新增 $filters 过滤参数（type / lang / author / sort）
    //   3. 新增右边栏数据：$recommended / $activeAuthors
    //   4. 新增 $filterOptions（过滤器选项 + 计数）
    //   5. 新增 $totalCount

    public function index(Request $request, ?int $id = null)
    {

        $locale = Cookie::get('language') ?? 'en';

        $categories = $this->loadCategories();

        // ── 当前分类 ──────────────────────────────────────────
        if ($id) {
            $currentCategory = collect($categories)->firstWhere('id', $id);
            if (! $currentCategory) {
                abort(404);
            }
            $breadcrumbs = $this->getBreadcrumbs($currentCategory, $categories);
        } else {
            // 首页：虚拟顶级分类
            $currentCategory = ['id' => null, 'name' => '三藏'];
            $breadcrumbs = [];
        }

        // ── 子分类 ─────────────────────────────────────────────
        $subCategories = array_values(array_filter(
            $categories,
            fn ($cat) => $cat['parent_id'] == $id
        ));
        if (count($subCategories) === 0 && ! $request->has('book')) {
            $paliBooks = $this->getPaliBooks($categories, $id);
            foreach ($paliBooks as $value) {
                $subCategories[] = [
                    'id' => $id,
                    'name' => $value->text,
                    'book' => "{$value->book}-{$value->paragraph}",
                ];
            }
        }
        $allNames = array_map(fn ($item) => $item['name'], $subCategories);

        // 去重
        $allNames = array_values(array_unique($allNames));

        // 查词典
        $terms = $this->termService->glossaryByLemma($allNames, $locale);
        // 构建映射
        $termMap = [];
        if ($terms) {
            foreach ($terms as $term) {
                $termMap[$term->word] = $term->meaning;
            }
        }
        // 回填
        foreach ($subCategories as $key => $cat) {
            $name = $cat['name'] ?? null;
            $subCategories[$key]['name'] = $termMap[$name] ?? $name;
        }

        // ── 过滤参数 ────────────────────────────────────────────
        $selectedType = request('type', 'all');
        $selectedLang = request('lang', 'all');
        $selectedAuthor = request('author', 'all');
        $selectedSort = request('sort', 'new');
        $selectedChannel = request('channel', 'all');

        // ── 当前频道（提供 channel 参数时） ──────────────────────
        $currentChannel = $selectedChannel !== 'all'
            ? (ChannelApi::getById($selectedChannel) ?: null)
            : null;

        $sortList = [
            ['key' => 'new',         'label' => __('library.badge_updated')],
            ['key' => 'progress',    'label' => '完成度'],
        ];

        $selected = [
            'type' => $selectedType,
            'lang' => $selectedLang,
            'author' => $selectedAuthor,
            'sort' => $selectedSort,
            'channel' => $selectedChannel,
        ];
        if ($request->has('book')) {
            $selected['book'] = $request->input('book');
        }

        // ── 书籍列表（过滤+排序，真实实现替换此处） ──────────────
        $categoryBooks = $this->getBooks($categories, $id, $selected);

        $totalCount = count($categoryBooks);

        // ── 过滤器选项（mock，真实实现从书籍数据聚合） ────────────
        $filterOptions = [
            'types' => $this->filterTypes(),
            'languages' => $this->filterLanguages(),
            'authors' => $this->getAuthorOptions($categoryBooks),
        ];

        // ── 右边栏：本周推荐（mock） ────────────────────────────
        $recommended = $this->mockRecommended();

        // ── 右边栏：活跃译者（mock） ────────────────────────────
        $activeAuthors = $this->mockActiveAuthors();

        $types = $this->types();

        return view('library.tipitaka.category', compact(
            'currentCategory',
            'subCategories',
            'categoryBooks',
            'breadcrumbs',
            'types',
            'selected',
            'filterOptions',
            'totalCount',
            'recommended',
            'activeAuthors',
            'sortList',
            'currentChannel'
        ));
    }

    private function filterLanguages()
    {
        return [
            ['value' => 'all',  'label' => '全部',   'count' => 0],
            ['value' => 'zh-Hans',   'label' => '简体中文',   'count' => 0],
            ['value' => 'zh-Hant',   'label' => '繁体中文',   'count' => 0],
            ['value' => 'pi',   'label' => '巴利语', 'count' => 0],
            ['value' => 'en',   'label' => '英语',   'count' => 0],
        ];
    }

    private function filterTypes()
    {
        return [
            ['value' => 'all',         'label' => '全部',    'count' => 0],
            ['value' => 'original',    'label' => '原文',    'count' => 0],
            ['value' => 'translation', 'label' => '译文',    'count' => 0],
            ['value' => 'nissaya',     'label' => 'Nissaya', 'count' => 0],
        ];
    }

    private function mockRecommended()
    {
        return [
            ['id' => 1, 'title' => '相应部·因缘篇',  'category' => '经藏'],
            ['id' => 2, 'title' => '法句经',          'category' => '经藏'],
            ['id' => 3, 'title' => '清净道论',        'category' => '注释'],
            ['id' => 4, 'title' => '律藏·波罗夷',    'category' => '律藏'],
            ['id' => 5, 'title' => '长部·梵网经',    'category' => '经藏'],
        ];
    }

    private function mockActiveAuthors()
    {
        return [
            [
                'name' => 'Bhikkhu Bodhi',
                'avatar' => null,
                'color' => '#2d5a8e',
                'initials' => 'BB',
                'count' => 24,
            ],
            [
                'name' => 'Bhikkhu Sujato',
                'avatar' => null,
                'color' => '#5a2d8e',
                'initials' => 'BS',
                'count' => 18,
            ],
        ];
    }

    // ── 辅助：从书籍列表聚合作者选项（mock，真实实现替换） ─────────
    private function getAuthorOptions(array $books): array
    {
        // TODO: 从 $books 聚合真实作者列表
        return [
            ['value' => 'all',             'label' => '全部作者',      'count' => count($books)],
            ['value' => 'bhikkhu-bodhi',   'label' => 'Bhikkhu Bodhi', 'count' => 0],
            ['value' => 'bhikkhu-sujato',  'label' => 'Bhikkhu Sujato', 'count' => 0],
            ['value' => 'buddhaghosa',     'label' => 'Buddhaghosa',   'count' => 0],
            ['value' => 'bhikkhu-brahmali', 'label' => 'Bhikkhu Brahmali', 'count' => 0],
        ];
    }

    private function types()
    {
        return [
            ['id' => '1', 'name' => 'sutta'],
            ['id' => '48', 'name' => 'vinaya'],
            ['id' => '66', 'name' => 'abhidhamma'],
            ['id' => '82', 'name' => 'añña'],
        ];
    }

    private function subCategories($categories, int $id)
    {
        return array_filter($categories, function ($cat) use ($id) {
            return $cat['parent_id'] == $id;
        });
    }

    private function getBooksIdInCat(array $categories, ?string $id)
    {
        if ($id) {
            $currentCategory = collect($categories)->firstWhere('id', $id);
            if (! $currentCategory) {
                abort(404);
            }
            // 标签查章节
            $tagNames = $currentCategory['tag'];
            $booksChapter = PaliText::withAllTags($tagNames)
                ->where('level', 1)->get();
        } else {
            $booksChapter = PaliText::select(['book', 'paragraph'])
                ->where('level', 1)
                ->get();
        }

        $chapters = [];
        foreach ($booksChapter as $key => $value) {
            $chapters[] = [$value->book, $value->paragraph];
        }

        return $chapters;
    }

    private function getPaliBooks(array $categories, string $id)
    {
        $chapters = $this->getBooksIdInCat($categories, $id);

        $books = PaliText::whereIns(['book', 'paragraph'], $chapters)->get();

        return $books;
    }

    private function getBooks(array $categories, ?string $id, array $filters)
    {
        // 根据分类获取书号
        if (isset($filters['book'])) {
            $chapters = [explode('-', $filters['book'])];
        } else {
            $chapters = $this->getBooksIdInCat($categories, $id);
        }

        $table = ProgressChapter::with('channel.owner')
            ->whereHas('channel', function ($query) use ($filters) {
                if ($filters['type'] !== 'all') {
                    $query->where('type', $filters['type'])
                        ->where('status', 30);
                }

                if ($filters['lang'] !== 'all') {
                    $query->where('lang', $filters['lang'])
                        ->where('status', 30);
                }

                if ($filters['channel'] !== 'all' && Str::isUuid($filters['channel'])) {
                    $query->where('uid', $filters['channel']);
                }
            })
            ->whereNotNull('last_chapter_completed_at')
            ->whereIns(['book', 'para'], $chapters);
        if ($filters['sort'] === 'new') {
            $table = $table->orderBy('last_chapter_completed_at', 'desc');
        } elseif ($filters['sort'] === 'progress') {
            $table = $table->orderBy('progress', 'desc');
        }
        $books = $table->take(100)->get();

        return $this->getBooksInfo($books);
    }

    private function getBooksInfo(mixed $books)
    {
        $pali = PaliText::where('level', 1)->get();
        // 获取该分类下的书籍
        $categoryBooks = [];
        $books->each(function ($book) use (&$categoryBooks, $pali) {
            $title = $book->title;
            if (empty($title)) {
                $title = $pali->firstWhere('book', $book->book)->toc;
            }

            $pcd_book_id = $pali->first(function ($item) use ($book) {
                return $item->book == $book->book
                    && $item->paragraph == $book->para;
            })?->pcd_book_id;

            $coverFile = "/assets/images/cover/zh-hans/1/{$pcd_book_id}.png";
            if (File::exists(public_path($coverFile))) {
                $coverUrl = $coverFile;
            } else {
                $coverUrl = null;
            }
            $colorIdx = $this->colorIndex($book->uid);
            $subTitle = $this->getBookType($book->book, $book->para);

            $categoryBooks[] = [
                'id' => $book->uid,
                'title' => $title,
                'author' => $book->channel->name,
                'subTitle' => $subTitle,
                'publisher' => $book->channel->owner,
                'completed_chapters' => $book->completed_chapters,
                'type' => __('labels.'.$book->channel->type),
                'cover' => $coverUrl,
                'cover_gradient' => $this->coverGradients[$colorIdx % count($this->coverGradients)],
                'description' => $book->summary ?? '比库戒律的详细说明',
                'language' => __('language.'.$book->channel->lang),
            ];
        });

        return $categoryBooks;
    }

    private function getBookType(int $book, int $para)
    {

        $paliTextUuid = PaliText::where('book', $book)->where('paragraph', $para)->value('uid');
        $tagIds = TagMap::where('anchor_id', $paliTextUuid)->select('tag_id')->get();
        $tags = Tag::whereIn('id', $tagIds)->select('name')->get();
        foreach ($tags as $key => $tag) {
            if (in_array($tag->name, ['pāḷi', 'aṭṭhakathā', 'ṭīkā'])) {
                return __('library.'.$tag->name);
            }
        }

        return null;
    }

    private function loadCategories()
    {
        $json = file_get_contents(public_path('data/category/default.json'));
        $tree = json_decode($json, true);
        $flat = self::flattenWithIds($tree);

        return $flat;
    }

    public static function flattenWithIds(array $tree, int $parentId = 0, int $level = 1): array
    {

        $flat = [];

        foreach ($tree as $node) {
            $currentId = self::$nextId++;

            $item = [
                'id' => $currentId,
                'parent_id' => $parentId,
                'name' => $node['name'] ?? null,
                'tag' => $node['tag'] ?? [],
                'description' => '佛教戒律经典',
                'level' => $level,
            ];

            $flat[] = $item;

            if (isset($node['children']) && is_array($node['children'])) {
                $childrenLevel = $level + 1;
                $flat = array_merge($flat, self::flattenWithIds($node['children'], $currentId, $childrenLevel));
            }
        }

        return $flat;
    }

    private function getBreadcrumbs($category, $categories)
    {
        $breadcrumbs = [];
        $current = $category;

        while ($current) {
            array_unshift($breadcrumbs, $current);
            $current = collect($categories)->firstWhere('id', $current['parent_id']);
        }

        return $breadcrumbs;
    }
}
