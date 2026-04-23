<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\DB;
use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Tag;
use App\Models\TagMap;


class CategoryController extends Controller
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
    // -------------------------------------------------------------------------
    // 从 uid / id 字符串中提取一个稳定的整数，用于色池取余
    // -------------------------------------------------------------------------
    private function colorIndex(string $uid): int
    {
        return hexdec(substr(str_replace('-', '', $uid), 0, 4)) % 255;
    }
    protected static int $nextId = 1;
    public function home()
    {
        $categories = $this->loadCategories();

        // 获取一级分类和对应的书籍
        $categoryData = [];
        foreach ($categories as $category) {
            if ($category['level'] == 1) {
                $children = $this->subCategories($categories, $category['id']);
                $categoryData[] = [
                    'category' => $category,
                    'children' => $children,
                ];
            }
        }
        $recentBooks = $this->getRecent();

        return view('library.index', compact(
            'categoryData',
            'categories',
            'recentBooks'
        ));
    }


    public function index()
    {
        $categories = $this->loadCategories();

        // 获取一级分类和对应的书籍
        $categoryData = [];
        foreach ($categories as $category) {
            if ($category['level'] == 1) {
                $children = $this->subCategories($categories, $category['id']);
                $categoryData[] = [
                    'category' => $category,
                    'children' => $children,
                ];
            }
        }

        return view('library.index', compact('categoryData', 'categories'));
    }

    // app/Http/Controllers/Library/CategoryController.php
    // category() 方法修改版
    // 变更：
    //   1. $id 改为可选参数，无参数时显示顶级分类（首页复用）
    //   2. 新增 $filters 过滤参数（type / lang / author / sort）
    //   3. 新增右边栏数据：$recommended / $activeAuthors
    //   4. 新增 $filterOptions（过滤器选项 + 计数）
    //   5. 新增 $totalCount

    public function category(?int $id = null)
    {

        $categories = $this->loadCategories();

        // ── 当前分类 ──────────────────────────────────────────
        if ($id) {
            $currentCategory = collect($categories)->firstWhere('id', $id);
            if (!$currentCategory) {
                abort(404);
            }
            $breadcrumbs = $this->getBreadcrumbs($currentCategory, $categories);
        } else {
            // 首页：虚拟顶级分类
            $currentCategory = ['id' => null, 'name' => '三藏'];
            $breadcrumbs     = [];
        }

        // ── 子分类 ─────────────────────────────────────────────
        $subCategories = array_values(array_filter(
            $categories,
            fn($cat) => $cat['parent_id'] == $id
        ));

        // ── 过滤参数 ────────────────────────────────────────────
        $selectedType   = request('type',   'all');
        $selectedLang   = request('lang',   'all');
        $selectedAuthor = request('author', 'all');
        $selectedSort   = request('sort',   'updated_at');

        $selected = [
            'type'   => $selectedType,
            'lang'   => $selectedLang,
            'author' => $selectedAuthor,
            'sort'   => $selectedSort,
        ];

        // ── 书籍列表（过滤+排序，真实实现替换此处） ──────────────
        $categoryBooks = $this->getBooks($categories, $id, $selected);
        // TODO: 将 $selected 传入 getBooks() 做实际过滤

        $totalCount = count($categoryBooks);

        // ── 过滤器选项（mock，真实实现从书籍数据聚合） ────────────
        $filterOptions = [
            'types' => [
                ['value' => 'all',         'label' => '全部',    'count' => $totalCount],
                ['value' => 'original',    'label' => '原文',    'count' => 0],
                ['value' => 'translation', 'label' => '译文',    'count' => 0],
                ['value' => 'nissaya',     'label' => 'Nissaya', 'count' => 0],
            ],
            'languages' => [
                ['value' => 'all',  'label' => '全部',   'count' => $totalCount],
                ['value' => 'zh-Hans',   'label' => '简体中文',   'count' => 0],
                ['value' => 'zh-Hant',   'label' => '繁体中文',   'count' => 0],
                ['value' => 'pi',   'label' => '巴利语', 'count' => 0],
                ['value' => 'en',   'label' => '英语',   'count' => 0],
            ],
            'authors' => $this->getAuthorOptions($categoryBooks),
        ];

        // ── 右边栏：本周推荐（mock） ────────────────────────────
        $recommended = [
            ['id' => 1, 'title' => '相应部·因缘篇',  'category' => '经藏'],
            ['id' => 2, 'title' => '法句经',          'category' => '经藏'],
            ['id' => 3, 'title' => '清净道论',        'category' => '注释'],
            ['id' => 4, 'title' => '律藏·波罗夷',    'category' => '律藏'],
            ['id' => 5, 'title' => '长部·梵网经',    'category' => '经藏'],
        ];

        // ── 右边栏：活跃译者（mock） ────────────────────────────
        $activeAuthors = [
            [
                'name'    => 'Bhikkhu Bodhi',
                'avatar'  => null,
                'color'   => '#2d5a8e',
                'initials' => 'BB',
                'count'   => 24,
            ],
            [
                'name'    => 'Bhikkhu Sujato',
                'avatar'  => null,
                'color'   => '#5a2d8e',
                'initials' => 'BS',
                'count'   => 18,
            ],
            [
                'name'    => 'Buddhaghosa',
                'avatar'  => null,
                'color'   => '#8e5a2d',
                'initials' => 'BG',
                'count'   => 12,
            ],
            [
                'name'    => 'Bhikkhu Brahmali',
                'avatar'  => null,
                'color'   => '#2d8e5a',
                'initials' => 'BR',
                'count'   => 9,
            ],
        ];

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
        ));
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
            ['id' => '82', 'name' => 'añña']
        ];
    }



    private function subCategories($categories, int $id)
    {
        return array_filter($categories, function ($cat) use ($id) {
            return $cat['parent_id'] == $id;
        });
    }
    private function getRecent()
    {
        return [
            [
                'id'         => 'book-001',
                'title'      => '相应部·因缘篇',
                'author'     => 'Bhikkhu Bodhi',
                'cover'      => null,                          // 无封面时显示渐变
                'cover_gradient' => 'linear-gradient(135deg, #2d5a8e 0%, #1a3a5c 100%)',
                'updated_at' => '2小时前',
                'is_new'     => true,                          // true=新增, false=更新
                'category'   => '经藏',
            ],
            [
                'id'         => 'book-002',
                'title'      => '长部·梵网经',
                'author'     => 'Bhikkhu Sujato',
                'cover'      => null,
                'cover_gradient' => 'linear-gradient(135deg, #5a2d8e 0%, #3a1a5c 100%)',
                'updated_at' => '昨天',
                'is_new'     => false,
                'category'   => '经藏',
            ],
            [
                'id'         => 'book-003',
                'title'      => '法句经注',
                'author'     => 'Buddhaghosa',
                'cover'      => null,
                'cover_gradient' => 'linear-gradient(135deg, #8e5a2d 0%, #5c3a1a 100%)',
                'updated_at' => '3天前',
                'is_new'     => false,
                'category'   => '注释',
            ],
            [
                'id'         => 'book-004',
                'title'      => '律藏·波罗夷',
                'author'     => 'Bhikkhu Brahmali',
                'cover'      => null,
                'cover_gradient' => 'linear-gradient(135deg, #2d8e5a 0%, #1a5c3a 100%)',
                'updated_at' => '5天前',
                'is_new'     => true,
                'category'   => '律藏',
            ],
            [
                'id'         => 'book-005',
                'title'      => '清净道论',
                'author'     => 'Buddhaghosa',
                'cover'      => null,
                'cover_gradient' => 'linear-gradient(135deg, #8e2d2d 0%, #5c1a1a 100%)',
                'updated_at' => '1周前',
                'is_new'     => false,
                'category'   => '注释',
            ],
            [
                'id'         => 'book-006',
                'title'      => '增支部·一集',
                'author'     => 'Bhikkhu Bodhi',
                'cover'      => null,
                'cover_gradient' => 'linear-gradient(135deg, #2d7a8e 0%, #1a4a5c 100%)',
                'updated_at' => '1周前',
                'is_new'     => false,
                'category'   => '经藏',
            ],
        ];
    }

    private function getUpdateBooks()
    {
        $books = ProgressChapter::with('channel.owner')
            ->leftJoin('pali_texts', function ($join) {
                $join->on('progress_chapters.book', '=', 'pali_texts.book')
                    ->on('progress_chapters.para', '=', 'pali_texts.paragraph');
            })
            ->whereHas('channel', function ($query) {
                $query->where('status', 30);
            })
            ->where('progress', '>', config('mint.library.list_min_progress'))
            ->take(10)
            ->get();

        return $this->getBooksInfo($books);
    }
    private function getBooks($categories, $id, $filters)
    {

        if ($id) {
            $currentCategory = collect($categories)->firstWhere('id', $id);
            if (!$currentCategory) {
                abort(404);
            }
            // 标签查章节
            $tagNames = $currentCategory['tag'];
            $tm = (new TagMap)->getTable();
            $tg = (new Tag)->getTable();
            $pt = (new PaliText)->getTable();
            $where1 = " where co = " . count($tagNames);
            $a = implode(",", array_fill(0, count($tagNames), '?'));
            $in1 = "and t.name in ({$a})";
            $param = $tagNames;
            $where2 = "where level = 1";
            $query = "select uid as id,book,paragraph,level,toc as title,chapter_strlen,parent,path from (
                            select anchor_id as cid from (
                                select tm.anchor_id , count(*) as co
                                    from $tm as  tm
                                    left join $tg as t on tm.tag_id = t.id
                                    where tm.table_name  = 'pali_texts'
                                    $in1
                                    group by tm.anchor_id
                            ) T
                                $where1
                        ) CID
                        left join $pt as pt on CID.cid = pt.uid
                        $where2
                        order by book,paragraph";

            $chapters = DB::select($query, $param);
            $chaptersParam = [];
            foreach ($chapters as $key => $chapter) {
                $chaptersParam[] = [$chapter->book, $chapter->paragraph];
            }
            // 获取该分类下的章节
            $books = ProgressChapter::with('channel.owner')
                ->whereIns(['progress_chapters.book', 'progress_chapters.para'], $chaptersParam)
                ->whereHas('channel', function ($query) {
                    $query->where('status', 30);
                })
                ->where('progress', '>', config('mint.library.list_min_progress'))
                ->get();
        } else {
            $booksChapter = PaliText::select(['book', 'paragraph'])->where('level', 1)->get();
            $chapters = [];
            foreach ($booksChapter as $key => $value) {
                $chapters[] = [$value->book, $value->paragraph];
            }
            $books = ProgressChapter::with('channel.owner')
                ->whereHas('channel', function ($query) use ($filters) {
                    $filters['type'] === 'all' ? $query->where('status', 30) :
                        $query->where('status', 30)->where('type', $filters['type']);
                })
                ->where('progress', '>', config('mint.library.list_min_progress'))
                ->whereIns(['book', 'para'], $chapters)
                ->take(100)
                ->get();
        }
        return $this->getBooksInfo($books);
    }

    private function getBooksInfo($books,)
    {
        $pali = PaliText::where('level', 1)->get();
        // 获取该分类下的书籍
        $categoryBooks = [];
        $books->each(function ($book) use (&$categoryBooks,  $pali) {
            $title = $book->title;
            if (empty($title)) {
                $title = $pali->firstWhere('book', $book->book)->toc;
            }
            //Log::debug('getBooksInfo', ['book' => $book->book, 'paragraph' => $book->para]);
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

            $categoryBooks[] = [
                "id" => $book->uid,
                "title" => $title,
                "author" => $book->channel->name,
                "publisher" => $book->channel->owner,
                "type" => __('labels.' . $book->channel->type),
                "cover" => $coverUrl,
                'cover_gradient' => $this->coverGradients[$colorIdx % count($this->coverGradients)],
                "description" => $book->summary ?? "比库戒律的详细说明",
                "language" => __('language.' . $book->channel->lang),
            ];
        });
        return $categoryBooks;
    }
    private function loadCategories()
    {
        $json = file_get_contents(public_path("data/category/default.json"));
        $tree = json_decode($json, true);
        $flat = self::flattenWithIds($tree);
        return $flat;
    }

    public static function flattenWithIds(array $tree,  int $parentId = 0, int $level = 1): array
    {

        $flat = [];

        foreach ($tree as $node) {
            $currentId = self::$nextId++;

            $item = [
                'id' => $currentId,
                'parent_id' => $parentId,
                'name' => $node['name'] ?? null,
                'tag' => $node['tag'] ?? [],
                "description" => "佛教戒律经典",
                'level' => $level,
            ];

            $flat[] = $item;

            if (isset($node['children']) && is_array($node['children'])) {
                $childrenLevel = $level + 1;
                $flat = array_merge($flat, self::flattenWithIds($node['children'],  $currentId, $childrenLevel));
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
