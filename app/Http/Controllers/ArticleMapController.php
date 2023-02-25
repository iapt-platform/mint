<?php

namespace App\Http\Controllers;

use App\Models\ArticleCollection;
use App\Models\Article;

use Illuminate\Http\Request;
use App\Http\Resources\ArticleMapResource;

class ArticleMapController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        switch ($request->get('view')) {
            case 'anthology':
                $table = ArticleCollection::where('collect_id',$request->get('id'));
                break;
            case 'article':
                $table = ArticleCollection::where('article_id',$request->get('id'));
                break;
        }
        $result = $table->select(['id','collect_id','article_id','level','title','children'])->orderBy('id')->get();
        return $this->ok(["rows"=>ArticleMapResource::collection($result),"count"=>count($result)]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validate([
                'anthology_id' => 'required',
                'operation' => 'required'
            ]);
        switch ($validated['operation']) {
            case 'add':
                # code...
                $count=0;
                foreach ($request->get('article_id') as $key => $article) {
                    # code...

                    if(!ArticleCollection::where('article_id',$article)
                                        ->where('collect_id',$request->get('anthology_id'))
                                        ->exists())
                    {
                        $new = new ArticleCollection;
                        $new->id = app('snowflake')->id();
                        $new->article_id = $article;
                        $new->collect_id = $request->get('anthology_id');
                        $new->title = Article::find($article)->title;
                        $new->level = 1;
                        $new->save();
                        $count++;
                    }
                }
                return $this->ok($count);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ArticleCollection  $articleCollection
     * @return \Illuminate\Http\Response
     */
    public function show(ArticleCollection $articleCollection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $id)
    {
        //
        $validated = $request->validate([
            'operation' => 'required'
        ]);
        switch ($validated['operation']) {
            case 'anthology':
                $delete = ArticleCollection::where('collect_id',$id)->delete();
                $count=0;
                foreach ($request->get('data') as $key => $row) {
                    # code...
                    $new = new ArticleCollection;
                    $new->id = app('snowflake')->id();
                    $new->article_id = $row["article_id"];
                    $new->collect_id = $id;
                    $new->title = $row["title"];
                    $new->level = $row["level"];
                    $new->children = $row["children"];
                    $new->save();
                    $count++;
                }
                return $this->ok($count);
                break;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ArticleCollection  $articleCollection
     * @return \Illuminate\Http\Response
     */
    public function destroy(ArticleCollection $articleCollection)
    {
        //
    }
}
