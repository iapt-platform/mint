<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\ArticleResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $indexCol = ['uid','title','subtitle','summary','owner','lang','status','updated_at','created_at'];
        switch ($request->get('view')) {
            case 'studio':
				# 获取studio内所有channel
                $user = \App\Http\Api\AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] === \App\Http\Api\StudioApi::getIdByName($request->get('name'))){
                        $table = Article::select($indexCol)->where('owner', $user["user_uid"]);
                    }else{
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
				break;
        }
        //处理搜索
        if($request->has("search") && !empty($request->has("search"))){
            $table = $table->where('title', 'like', "%".$request->get("search")."%");
        }
        //获取记录总条数
        $count = $table->count();
        //处理排序
        if(isset($_GET["order"]) && isset($_GET["dir"])){
            $table = $table->orderBy($_GET["order"],$_GET["dir"]);
        }else{
            //默认排序
            $table = $table->orderBy('updated_at','desc');
        }
        //处理分页
        if($request->has("limit")){

            if($request->has("offset")){
                $offset = $request->get("offset");
            }else{
                $offset = 0;
            }
            $table = $table->skip($offset)->take($request->get("limit"));
        }
        //获取数据
        $result = $table->get();
        if($result){
            /*
            foreach ($result as $key => $value) {
                # 获取studio信息
                $studio = $userinfo->getName($value->owner_uid);
                $value->studio = [
                    'id'=>$value->owner_uid,
                    'nickName'=>$studio['nickname'],
                    'studioName'=>$studio['username'],
                    'avastar'=>'',
                    'owner' => [
                        'id'=>$value->owner_uid,
                        'nickName'=>$studio['nickname'],
                        'userName'=>$studio['username'],
                        'avastar'=>'',
                    ]
                ];
            }*/
			return $this->ok(["rows"=>$result,"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
		}
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
        $user = \App\Http\Api\AuthApi::current($request);
        if($user){
            //判断当前用户是否有指定的studio的权限
            if($user['user_uid'] === \App\Http\Api\StudioApi::getIdByName($request->get('studio'))){
                //查询是否重复
                if(Article::where('title',$request->get('title'))->where('owner',$user['user_uid'])->exists()){
                    return $this->error(__('validation.exists'));
                }else{

                    $newOne = new Article;
                    $newOne->id = app('snowflake')->id();
                    $newOne->uid = Str::uuid();
                    $newOne->title = $request->get('title');
                    $newOne->lang = $request->get('lang');
                    $newOne->owner = $user['user_uid'];
                    $newOne->owner_id = $user['user_id'];
                    $newOne->editor_id = $user['user_id'];
                    $newOne->create_time = time()*1000;
                    $newOne->modify_time = time()*1000;
                    $newOne->save();
                    return $this->ok($newOne);
                }
            }else{
                return $this->error(__('auth.failed'));
            }
        }else{
            return $this->error(__('auth.failed'));
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
        if($article->status<30){
            //私有文章，判断权限
            $user = \App\Http\Api\AuthApi::current($request);
            if(!$user){
                //判断当前用户是否有指定的studio的权限
                return $this->error(__('auth.failed'));
            }
            if($user['user_uid'] !== $article->owner){
                //非所有者
                return $this->error(__('auth.failed'));
            }else{
                //TODO 判断是否协作
            }
        }
        return $this->ok(new ArticleResource($article));
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
        if($article){
            //鉴权
            $user = \App\Http\Api\AuthApi::current($request);
            if($user && $article->owner === $user["user_uid"]){
                $article->title = $request->get('title');
                $article->subtitle = $request->get('subtitle');
                $article->summary = $request->get('summary');
                $article->content = $request->get('content');
                $article->lang = $request->get('lang');
                $article->status = $request->get('status');
                $article->modify_time = time()*1000;
                $article->save();
                return $this->ok($article);
            }else{
                //鉴权失败
                //TODO 判断是否为协作
                return $this->error(__('auth.failed'));
            }

        }else{
            return $this->error("no recorder");
        }

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
        });

        return $this->ok($delete);
    }
}
