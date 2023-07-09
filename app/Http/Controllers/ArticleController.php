<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\Collection;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\ArticleResource;
use App\Http\Api\AuthApi;
use App\Http\Api\ShareApi;
use App\Http\Api\StudioApi;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    public static function userCanRead($user_uid,Article $article){
        if($article->status === 30 ){
            return true;
        }
        if(empty($user_uid)){
            return false;
        }
            //私有文章，判断是否为所有者
        if($user_uid === $article->owner){
            return true;
        }
        //非所有者
        //判断是否为文章协作者
        $power = ShareApi::getResPower($user_uid,$article->uid);
        if($power >= 10 ){
            return true;
        }
        //无读取权限
        //判断文集是否有读取权限
        $inCollection = ArticleCollection::where('article_id',$article->uid)
                                        ->select('collect_id')
                                        ->groupBy('collect_id')->get();
        if(!$inCollection){
            return false;
        }
        //查找与文章同主人的文集
        $collections = Collection::whereIn('uid',$inCollection)
                                    ->where('owner',$article->owner)
                                    ->select('uid')
                                    ->get();
        if(!$collections){
            return false;
        }
        //查找与文章同主人的文集是否是共享的
        $power = 0;
        foreach ($collections as $collection) {
            # code...
            $currPower = ShareApi::getResPower($user_uid,$collection->uid);
            if($currPower >= 10){
                return true;
            }
        }
        return false;
    }

    public static function userCanEdit($user_uid,$article){
        if(empty($user_uid)){
            return false;
        }
        //私有文章，判断是否为所有者
        if($user_uid === $article->owner){
            return true;
        }
        //非所有者
        //判断是否为文章协作者
        $power = ShareApi::getResPower($user_uid,$article->uid);
        if($power >= 20 ){
            return true;
        }
        //无读取权限
        //判断文集是否有读取权限
        $inCollection = ArticleCollection::where('article_id',$article->uid)
                                        ->select('collect_id')
                                        ->groupBy('collect_id')->get();
        if(!$inCollection){
            return false;
        }
        //查找与文章同主人的文集
        $collections = Collection::whereIn('uid',$inCollection)
                                    ->where('owner',$article->owner)
                                    ->select('uid')
                                    ->get();
        if(!$collections){
            return false;
        }
        //查找与文章同主人的文集是否是共享的
        $power = 0;
        foreach ($collections as $collection) {
            # code...
            $currPower = ShareApi::getResPower($user_uid,$collection->uid);
            if($currPower >= 20){
                return true;
            }
        }
        return false;
    }

    public static function userCanManage($user_uid,$studioName){
        if(empty($user_uid)){
            return false;
        }
        //判断是否为所有者
        if($user_uid === StudioApi::getIdByName($studioName)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $table = Article::select(['uid','title','subtitle',
                                'summary','owner','lang',
                                'status','updated_at','created_at']);
        switch ($request->get('view')) {
            case 'studio':
				# 获取studio内所有channel
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'),[],401);
                }
                //判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->get('name'));
                if($user['user_uid'] !== $studioId){
                    return $this->error(__('auth.failed'),[],403);
                }

                if($request->get('view2','my')==='my'){
                    $table = $table->where('owner', $studioId);
                }else{
                    //协作
                    $resList = ShareApi::getResList($studioId,3);
                    $resId=[];
                    foreach ($resList as $res) {
                        $resId[] = $res['res_id'];
                    }
                    $table = $table->whereIn('uid', $resId)->where('owner','<>', $studioId);
                }

                //根据anthology过滤
                if($request->has('anthology')){
                    switch ($request->get('anthology')) {
                        case 'all':
                            break;
                        case 'none':
                            # 我的文集
                            $myCollection = Collection::where('owner',$studioId)->select('uid')->get();
                            //收录在我的文集里面的文章
                            $articles = ArticleCollection::whereIn('collect_id',$myCollection)
                                                         ->select('article_id')->groupBy('article_id')->get();
                            //不在这些范围之内的文章
                            $table =  $table->whereNotIn('uid',$articles);
                            break;
                        default:
                            $articles = ArticleCollection::where('collect_id',$request->get('anthology'))
                                                         ->select('article_id')->get();
                            $table =  $table->whereIn('uid',$articles);
                            break;
                    }
                }
				break;
            case 'public':
                $table = $table->where('status',30);
                break;
            default:
                $this->error("view error");
                break;
        }
        //处理搜索
        if($request->has("search") && !empty($request->has("search"))){
            $table = $table->where('title', 'like', "%".$request->get("search")."%");
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        $table = $table->orderBy($request->get("order",'updated_at'),
                                 $request->get("dir",'desc'));
        //处理分页
        $table = $table->skip($request->get("offset",0))
                       ->take($request->get("limit",1000));
        //获取数据
        $result = $table->get();
		return $this->ok(["rows"=>ArticleResource::collection($result),"count"=>$count]);
    }

        /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showMyNumber(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        $studioId = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studioId){
            return $this->error(__('auth.failed'));
        }
        //我的
        $my = Article::where('owner', $studioId)->count();
        //协作
        $resList = ShareApi::getResList($studioId,3);
        $resId=[];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $collaboration = Article::whereIn('uid', $resId)->where('owner','<>', $studioId)->count();

        return $this->ok(['my'=>$my,'collaboration'=>$collaboration]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //判断权限
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[],401);
        }else{
            $user_uid=$user['user_uid'];
        }

        $canManage = ArticleController::userCanManage($user_uid,$request->get('studio'));
        if(!$canManage){
            return $this->error(__('auth.failed'),[],403);
        }
        //权限判断结束

        //查询标题是否重复
        /*
        if(Article::where('title',$request->get('title'))->where('owner',$studioUuid)->exists()){
            return $this->error(__('validation.exists'));
        }*/
        $newArticle = new Article;
        DB::transaction(function() use($user,$request,$newArticle){
            $studioUuid = StudioApi::getIdByName($request->get('studio'));
            //新建文章，加入文集必须都成功。否则回滚
            $newArticle->id = app('snowflake')->id();
            $newArticle->uid = Str::uuid();
            $newArticle->title = $request->get('title');
            $newArticle->lang = $request->get('lang');
            $newArticle->owner = $studioUuid;
            $newArticle->owner_id = $user['user_id'];
            $newArticle->editor_id = $user['user_id'];
            $newArticle->create_time = time()*1000;
            $newArticle->modify_time = time()*1000;
            $newArticle->save();

            if(Str::isUuid($request->get('anthologyId'))){
                $articleMap = new ArticleCollection();
                $articleMap->id = app('snowflake')->id();
                $articleMap->article_id = $newArticle->uid;
                $articleMap->collect_id = $request->get('anthologyId');
                $articleMap->title = Article::find($newArticle->uid)->title;
                $articleMap->level = 1;
                $articleMap->save();
            }
        });
        if(Str::isUuid($newArticle->uid)){
            return $this->ok($newArticle);
        }else{
            return $this->error('fail');
        }

    }

    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request,Article $article)
    {
        //
        if(!$article){
            return $this->error("no recorder");
        }
        //判断权限
        $user = AuthApi::current($request);
        if(!$user){
            $user_uid="";
        }else{
            $user_uid=$user['user_uid'];
        }

        $canRead = ArticleController::userCanRead($user_uid,$article);
        if(!$canRead){
            return $this->error(__('auth.failed'),[],401);
        }
        return $this->ok(new ArticleResource($article));
    }
    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $article
     * @return \Illuminate\Http\Response
     */
    public function preview(Request  $request,string $articleId)
    {
        //
        $article = Article::find($articleId);
        if(!$article){
            return $this->error("no recorder");
        }
        //判断权限
        $user = AuthApi::current($request);
        if(!$user){
            $user_uid="";
        }else{
            $user_uid=$user['user_uid'];
        }

        $canRead = ArticleController::userCanRead($user_uid,$article);
        if(!$canRead){
            return $this->error(__('auth.failed'),[],401);
        }
        if($request->has('content')){
            $article->content = $request->get('content');
            return $this->ok(new ArticleResource($article));
        }else{
            return $this->error('no content',[],200);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        //
        if(!$article){
            return $this->error("no recorder");
        }
        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),[],401);
        }else{
            $user_uid=$user['user_uid'];
        }

        $canEdit = ArticleController::userCanEdit($user_uid,$article);
        if(!$canEdit){
            return $this->error(__('auth.failed'),[],401);
        }

        /*
        //查询标题是否重复
        if(Article::where('title',$request->get('title'))
                  ->where('owner',$article->owner)
                  ->where('uid',"<>",$article->uid)
                  ->exists()){
            return $this->error(__('validation.exists'));
        }*/

        $article->title = $request->get('title');
        $article->subtitle = $request->get('subtitle');
        $article->summary = $request->get('summary');
        $article->content = $request->get('content');
        $article->lang = $request->get('lang');
        $article->status = $request->get('status',10);
        $article->editor_id = $user['user_id'];
        $article->modify_time = time()*1000;
        $article->save();
        return $this->ok($article);

    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Article $article)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if($user['user_uid'] !== $article->owner){
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        DB::transaction(function() use($article,$delete){
            //TODO 删除文集中的文章
            $delete = $article->delete();
            ArticleMapController::deleteArticle($article->uid);
        });

        return $this->ok($delete);
    }
}
