<?php

namespace App\Http\Controllers;

use App\Http\Resources\ArticleMapResource;
use App\Models\ArticleCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NavArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
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
     * 文章导航
     * 文集中 某个文章的 前一个，后一个
     *
     * @param  ArticleCollection  $articleCollection
     * @return Response
     */
    public function show(string $id)
    {
        // article_anthology
        $id = explode('_', $id);
        if (count($id) !== 2) {
            return $this->error('参数错误。参数应为 2 实际得到'.count($id), 400, 400);
        }
        $curr = ArticleCollection::where('collect_id', $id[1])
            ->where('article_id', $id[0])
            ->first();
        if (! $curr) {
            return $this->error('article not found');
        }
        $data = [];
        $data['curr'] = new ArticleMapResource($curr);
        $prev = ArticleCollection::where('collect_id', $id[1])
            ->where('id', '<', $curr->id)
            ->orderBy('id', 'desc')
            ->first();
        if ($prev) {
            $data['prev'] = new ArticleMapResource($prev);
        }
        $next = ArticleCollection::where('collect_id', $id[1])
            ->where('id', '>', $curr->id)
            ->orderBy('id')
            ->first();
        if ($next) {
            $data['next'] = new ArticleMapResource($next);
        }

        return $this->ok($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, ArticleCollection $articleCollection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(ArticleCollection $articleCollection)
    {
        //
    }
}
