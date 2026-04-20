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
        $update = $this->getUpdateBooks();

        return view('library.index', compact('categoryData', 'categories', 'update'));
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
    public function category(int $id)
    {

        $categories = $this->loadCategories();

        $currentCategory = collect($categories)->firstWhere('id', $id);
        if (!$currentCategory) {
            abort(404);
        }

        // 获取子分类
        $subCategories = array_filter($categories, function ($cat) use ($id) {
            return $cat['parent_id'] == $id;
        });

        // 获取该分类下的书籍
        $categoryBooks = $this->getBooks($categories, $id);
        // 获取面包屑
        $breadcrumbs = $this->getBreadcrumbs($currentCategory, $categories);

        return view('library.tipitaka.category', compact('currentCategory', 'subCategories', 'categoryBooks', 'breadcrumbs'));
    }



    private function subCategories($categories, int $id)
    {
        return array_filter($categories, function ($cat) use ($id) {
            return $cat['parent_id'] == $id;
        });
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
    private function getBooks($categories, $id)
    {
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
        $query = "
                        select uid as id,book,paragraph,level,toc as title,chapter_strlen,parent,path from (
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
