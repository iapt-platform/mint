<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;


use App\Models\PaliText;
use App\Models\ProgressChapter;
use App\Models\Tag;
use App\Models\TagMap;


class HomeController extends Controller
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
        $recentBooks = $this->getUpdateBooks();

        return view('library.index', compact(
            'categoryData',
            'categories',
            'recentBooks'
        ));
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


    private function getBooksInfo($books)
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

                'updated_at' => $book->updated_at,
                'is_new'     => false, //FIXME
                'category'   => '经藏', //FIXME
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
}
