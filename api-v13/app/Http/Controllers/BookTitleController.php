<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookTitleResource;
use App\Models\BookTitle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class BookTitleController extends Controller
{
    /**
     * 书目清单基本不变，而每次都要连 pali_texts / tag_maps / tags 三张表，
     * 所以整份结果缓存 24 小时。
     */
    private const CACHE_KEY = 'book-titles/with-tags';

    private const CACHE_TTL = 60 * 60 * 24;

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
        $data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $result = BookTitle::orderBy('sn')->get();
            $meta = $this->paliTextMeta($result);
            $relatedNames = $this->relatedNames($result);
            foreach ($result as $row) {
                $key = "{$row->book}-{$row->paragraph}";
                $row->toc = $meta[$key]['toc'] ?? null;
                $row->tags = $meta[$key]['tags'] ?? [];
                $row->related_name = $relatedNames[$key] ?? null;
            }

            return [
                'rows' => BookTitleResource::collection($result)->resolve(),
                'count' => count($result),
            ];
        });

        return $this->ok($data);
    }

    /**
     * 取每条书目在 pali_texts 里对应的 toc 与 tag 名。
     *
     * book_titles 的 (book, paragraph) 指向 pali_texts 的同名字段；toc 直接取自
     * pali_texts，tag 则再经 pali_texts.uid → tag_maps.anchor_id → tags 一跳。
     *
     * 两条查询都按 (book, paragraph) 的取值范围收窄，一次查完在内存里归组，
     * 避免 281 条书目各查一次。whereIn 取的是两个维度的笛卡尔超集，最后按精确的
     * "{book}-{paragraph}" 键取用，多出来的行不会被匹配到。
     *
     * @param  Collection  $bookTitles
     * @return array<string, array{toc: ?string, tags: string[]}> 键是 "{book}-{paragraph}"
     */
    private function paliTextMeta($bookTitles): array
    {
        if ($bookTitles->isEmpty()) {
            return [];
        }

        $books = $bookTitles->pluck('book')->unique()->all();
        $paragraphs = $bookTitles->pluck('paragraph')->unique()->all();

        $texts = DB::table('pali_texts')
            ->whereIn('book', $books)
            ->whereIn('paragraph', $paragraphs)
            ->select('uid', 'book', 'paragraph', 'toc')
            ->get();

        $meta = [];
        $keyByUid = [];
        foreach ($texts as $text) {
            $key = "{$text->book}-{$text->paragraph}";
            $meta[$key] = ['toc' => $text->toc, 'tags' => []];
            $keyByUid[$text->uid] = $key;
        }
        if (empty($keyByUid)) {
            return $meta;
        }

        $tags = DB::table('tag_maps')
            ->join('tags', 'tags.id', '=', 'tag_maps.tag_id')
            ->where('tag_maps.table_name', 'pali_texts')
            ->whereIn('tag_maps.anchor_id', array_keys($keyByUid))
            ->select('tag_maps.anchor_id', 'tags.name')
            ->get();

        foreach ($tags as $tag) {
            $key = $keyByUid[$tag->anchor_id] ?? null;
            if ($key === null || in_array($tag->name, $meta[$key]['tags'], true)) {
                continue;
            }
            $meta[$key]['tags'][] = $tag->name;
        }
        foreach ($meta as &$item) {
            sort($item['tags']);
        }

        return $meta;
    }

    /**
     * 取每条书目对应的 CST 书名（related_paragraphs.book_name）。
     *
     * 按 (book, para) 配对，而不是 related_paragraphs.book_id = book_titles.sn。
     * 两者实测差异：book_id 给的是「这本书横跨的全部 CST 书」（pācityādiyojanā →
     * vin2..vin5），(book, para) 给的是「起始段所在的那一本」（→ vin2），且后者能查到
     * book_id 归错地方的 samantapāsādikā(sn=280) 与 Bhikkhunīvibhaṅga(sn=281)。
     *
     * 实测每个 (book, para) 至多对应一个非空 book_name，故返回标量。
     *
     * @param  Collection  $bookTitles
     * @return array<string, string> 键是 "{book}-{paragraph}"
     */
    private function relatedNames($bookTitles): array
    {
        if ($bookTitles->isEmpty()) {
            return [];
        }

        $rows = DB::table('related_paragraphs')
            ->whereIn('book', $bookTitles->pluck('book')->unique()->all())
            ->whereIn('para', $bookTitles->pluck('paragraph')->unique()->all())
            ->whereNotNull('book_name')
            ->where('book_name', '<>', '')
            ->select('book', 'para', 'book_name')
            ->distinct()
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map["{$row->book}-{$row->para}"] = $row->book_name;
        }

        return $map;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(BookTitle $bookTitle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, BookTitle $bookTitle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(BookTitle $bookTitle)
    {
        //
    }
}
