<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\SearchBookResource;
use App\Http\Resources\SearchPaliWbwResource;
use App\Models\WbwTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SearchPaliWbwController extends Controller
{
    /**
     * 把 key 拆成词表，丢掉切出来的空串。
     *
     * SearchRequest 只保证 key 里有内容，`dhammo,,` 照样能通过；而空串一旦进了
     * whereIn('real', ...)，就会命中 real 为空的那四百多万行、覆盖 49 万个段落，
     * 把无关结果混进命中里。
     *
     * @return string[]
     */
    private function keywords(?string $key): array
    {
        $words = array_map('trim', explode(',', (string) $key));

        return array_values(array_filter($words, fn (string $word): bool => $word !== ''));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(SearchRequest $request)
    {
        // 获取书的范围
        $bookId = [];
        $search = new SearchController;
        if ($request->has('book')) {
            foreach (explode(',', $request->input('book')) as $key => $id) {
                $bookId[] = (int) $id;
            }
        } elseif ($request->has('tags')) {
            // 查询搜索范围
            // 查询搜索范围
            $tagItems = explode(';', $request->input('tags'));

            foreach ($tagItems as $tagItem) {
                $bookId = array_merge($bookId, $search->getBookIdByTags(explode(',', $tagItem)));
            }
        }

        $keyWords = $this->keywords($request->input('key'));
        $table = WbwTemplate::whereIn('real', $keyWords)
            ->groupBy(['book', 'paragraph'])
            ->selectRaw('book,paragraph,sum(weight) as rank');
        $whereBold = '';
        if ($request->input('bold') === 'on') {
            $table = $table->where('style', 'bld');
            $whereBold = " and style='bld'";
        } elseif ($request->input('bold') === 'off') {
            $table = $table->where('style', '<>', 'bld');
            $whereBold = " and style <> 'bld'";
        }
        $placeholderWord = implode(',', array_fill(0, count($keyWords), '?'));
        $whereWord = "real in ({$placeholderWord})";
        $whereBookId = '';
        if (count($bookId) > 0) {
            $table = $table->whereIn('pcd_book_id', $bookId);
            $placeholderBookId = implode(',', array_fill(0, count($bookId), '?'));
            $whereBookId = " and pcd_book_id in ({$placeholderBookId}) ";
        }
        $queryCount = "SELECT count(*) FROM ( SELECT book,paragraph FROM wbw_templates WHERE $whereWord $whereBookId $whereBold  GROUP BY book,paragraph) T;";
        $count = DB::select($queryCount, array_merge($keyWords, $bookId));

        $table = $table->orderBy('rank', 'desc');
        $table = $table->skip($request->input('offset', 0))
            ->take($request->input('limit', 10));

        $result = $table->get();

        return $this->ok([
            'rows' => SearchPaliWbwResource::collection($result),
            'count' => $count[0]->count,
        ]);
    }

    public function book_list(SearchRequest $request)
    {
        // 获取书的范围
        $bookId = [];
        $search = new SearchController;
        if ($request->has('tags')) {
            // 查询搜索范围
            // 查询搜索范围
            $tagItems = explode(';', $request->input('tags'));

            foreach ($tagItems as $tagItem) {
                $bookId = array_merge($bookId, $search->getBookIdByTags(explode(',', $tagItem)));
            }
        }
        $keyWords = $this->keywords($request->input('key'));
        $table = WbwTemplate::whereIn('real', $keyWords);

        if (count($bookId) > 0) {
            $table = $table->whereIn('pcd_book_id', $bookId);
        }
        $table = $table->groupBy('pcd_book_id')
            ->selectRaw('pcd_book_id,count(*) as co')
            ->orderBy('co', 'desc');
        $result = $table->get();

        return $this->ok(['rows' => SearchBookResource::collection($result), 'count' => count($result)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(WbwTemplate $wbwTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, WbwTemplate $wbwTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(WbwTemplate $wbwTemplate)
    {
        //
    }
}
