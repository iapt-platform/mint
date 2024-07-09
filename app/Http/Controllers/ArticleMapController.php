<?php

namespace App\Http\Controllers;

use App\Models\ArticleCollection;
use App\Models\Article;
use App\Models\Collection;
use App\Http\Api\ShareApi;
use App\Http\Api\AuthApi;
use Illuminate\Http\Request;
use App\Http\Resources\ArticleMapResource;
use Illuminate\Support\Facades\Log;

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
        $count = $table->count();
        $result = [];
        if(!empty($request->get('parent'))){
            //输出某节点的子节点
            $node = $table->where('article_id',$request->get('parent'))->first();
            if($node){
                $nodeList = ArticleCollection::where('collect_id',$request->get('id'))
                                            ->where('id','>',(int)$node->id)->orderBy('id')->get();
                foreach ($nodeList as $key => $curr) {
                    if($curr->level <= $node->level){
                        break;
                    }
                    if($request->has('lazy')){
                        if($curr->level === $node->level+1){
                            $result[] = $curr;
                        }
                    }else{
                        $result[] = $curr;
                    }
                }
            }
        }else{
            if($request->has('lazy') && $count > 300){
                $table = $table->where('level',1);
            }
            $result = $table->select(['id','collect_id','article_id','level','title','children','editor_id','deleted_at'])
                        ->orderBy('id')->get();
        }

        return $this->ok(["rows"=>ArticleMapResource::collection($result),"count"=>$count]);
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
        $collection  = Collection::find($request->get('anthology_id'));
        if(!$collection){
            return $this->error("no recorder");
        }
        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        if(!CollectionController::UserCanEdit($user["user_uid"],$collection)){
            Log::error($user["user_uid"].'无文集编辑权限'.$collection->uid);
            return $this->error(__('auth.failed'));
        }
        switch ($validated['operation']) {
            case 'add':
                # 添加多个文章到文集
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
                        $new->editor_id = $user["user_id"];
                        $new->save();
                        $count++;
                    }
                }
                return $this->ok($count);
                break;
            default:
                return $this->error('unknown operation');
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ArticleCollection  $articleCollection
     * @return \Illuminate\Http\Response
     */
    public function show(string $articleCollection)
    {
        //
        $id = explode('_',$articleCollection);
        $result = ArticleCollection::where('article_id',$id[0])
                    ->where('collect_id',$id[1])
                    ->first();
        if($result){
            return $this->ok(new ArticleMapResource($result));
        }else{
            return $this->error('no');
        }

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

        $collection  = Collection::find($id);
        if(!$collection){
            return $this->error("no recorder");
        }
        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        if(!CollectionController::UserCanEdit($user["user_uid"],$collection)){
            return $this->error(__('auth.failed'));
        }

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
                    $new->editor_id = $user["user_id"];
                    if(isset($row["deleted_at"])){
                        $new->deleted_at = $row["deleted_at"];
                    }
                    $new->save();
                    $count++;
                }
                ArticleMapController::updateCollection($id);
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

    public static function deleteArticle(string $articleId){
        //查找有这个文章的文集
        $collections = ArticleCollection::where('article_id',$articleId)
                                        ->select('collect_id')
                                        ->groupBy('collect_id')
                                        ->get();
        //设置为删除
        ArticleCollection::where('article_id',$articleId)
                         ->update(['deleted_at'=>now()]);
        //查找没有下级文章的文集
        $updateCollections = ArticleCollection::where('article_id',$articleId)
                                            ->where('children',0)
                                            ->select('collect_id')
                                            ->groupBy('collect_id')
                                            ->get();
        //真的删除没有下级文章的文集中的文章
        $count = ArticleCollection::where('article_id',$articleId)
                                  ->where('children',0)
                                  ->delete();
        //更新改动的文集
        foreach ($updateCollections as  $collection) {
            # code...
            ArticleMapController::updateCollection($collection->collect_id);
        }
        return [count($collections),$count];
    }

    public static function deleteCollection(string $collectionId){
        $count = ArticleCollection::where('collect_id',$collectionId)
                                  ->delete();
        return $count;
    }

    /**
     * 用表中的数据生成json，更新collection 表中的字段
     */
    public static function updateCollection(string $collectionId){
        $result = ArticleCollection::where('collect_id',$collectionId)
                        ->select(['article_id','level','title'])
                        ->orderBy('id')->get();
        Collection::where('uid',$collectionId)
                  ->update(['article_list'=>json_encode($result,JSON_UNESCAPED_UNICODE)]);
        return count($result);
    }
}
