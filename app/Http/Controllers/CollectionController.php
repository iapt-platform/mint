<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Api\ShareApi;
use App\Http\Resources\CollectionResource;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

		$result=false;
		$indexCol = ['uid','title','subtitle','summary',
                      'article_list','owner','status',
                      'default_channel','lang',
                      'updated_at','created_at'];
		switch ($request->get('view')) {
            case 'studio_list':
		        $indexCol = ['owner'];
                //TODO ?
                $table = Collection::select($indexCol)
                                    ->selectRaw('count(*) as count')
                                    ->where('status', 30)
                                    ->groupBy('owner');
                break;
			case 'studio':
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $studioId = StudioApi::getIdByName($request->get('name'));
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== $studioId){
                    return $this->error(__('auth.failed'));
                }
                $table = Collection::select($indexCol);
                if($request->get('view2','my')==='my'){
                    $table = $table->where('owner', $studioId);
                }else{
                    //协作
                    $resList = ShareApi::getResList($studioId,4);
                    $resId=[];
                    foreach ($resList as $res) {
                        $resId[] = $res['res_id'];
                    }
                    $table = $table->whereIn('uid', $resId)->where('owner','<>', $studioId);
                }
				break;
			case 'public':
                //全网公开
				$table = Collection::select($indexCol)->where('status', 30);
                if($request->has('studio')){
                    $studioId = StudioApi::getIdByName($request->get('studio'));
                    $table = $table->where('owner',$studioId);
                }
				break;
			default:
				# code...
			    return $this->error("无法识别的view参数",200,200);
				break;
		}
        if($request->has("search") && !empty($request->has("search"))){
            $table = $table->where('title', 'like', "%".$request->get("search")."%");
        }
        $count = $table->count();
        if($request->has("order") && $request->has("dir")){
            $table = $table->orderBy($request->get("order"),$request->get("dir"));
        }else{
            if($request->get('view') === 'studio_list'){
                $table = $table->orderBy('count','desc');
            }else{
                $table = $table->orderBy('updated_at','desc');
            }
        }

        $table = $table->skip($request->get("offset",0))
                       ->take($request->get("limit",1000));

        $result = $table->get();
		return $this->ok(["rows"=>CollectionResource::collection($result),"count"=>$count]);
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
        $my = Collection::where('owner', $studioId)->count();
        //协作
        $resList = ShareApi::getResList($studioId,4);
        $resId=[];
        foreach ($resList as $res) {
            $resId[] = $res['res_id'];
        }
        $collaboration = Collection::whereIn('uid', $resId)->where('owner','<>', $studioId)->count();

        return $this->ok(['my'=>$my,'collaboration'=>$collaboration]);
    }

    public static function UserCanEdit($user_uid,$collection){
        if($collection->owner === $user_uid){
            return true;
        }
        //查协作
        $currPower = ShareApi::getResPower($user_uid,$collection->uid);
        if($currPower >= 20){
            return true;
        }
        return false;
    }
    public static function UserCanRead($user_uid,$collection){
        if($collection->owner === $user_uid){
            return true;
        }
        //查协作
        $currPower = ShareApi::getResPower($user_uid,$collection->uid);
        if($currPower >= 10){
            return true;
        }
        return false;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = \App\Http\Api\AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        //判断当前用户是否有指定的studio的权限
        if($user['user_uid'] !== \App\Http\Api\StudioApi::getIdByName($request->get('studio'))){
            return $this->error(__('auth.failed'),403,403);
        }
        //查询是否重复
        if(Collection::where('title',$request->get('title'))->where('owner',$user['user_uid'])->exists()){
            return $this->error(__('validation.exists'),200,200);
        }else{
            $newOne = new Collection;
            $newOne->id = app('snowflake')->id();
            $newOne->uid = Str::uuid();
            $newOne->title = $request->get('title');
            $newOne->lang = $request->get('lang');
            $newOne->article_list = "[]";
            $newOne->owner = $user['user_uid'];
            $newOne->owner_id = $user['user_id'];
            $newOne->editor_id = $user['user_id'];
            $newOne->create_time = time()*1000;
            $newOne->modify_time = time()*1000;
            $newOne->save();
            return $this->ok(new CollectionResource($newOne));
        }
    }

    /**
     * Display the specified resource.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request,$id)
    {
		$result  = Collection::where('uid', $id)->first();
		if(!$result){
            return $this->error("没有查询到数据");
        }
        if($result->status<30){
            //私有文章，判断权限
            Log::error('私有文章，判断权限'.$id);
            $user = \App\Http\Api\AuthApi::current($request);
            if(!$user){
                Log::error('未登录');
                return $this->error(__('auth.failed'),401,401);
            }
            //判断当前用户是否有指定的studio的权限
            if($user['user_uid'] !== $result->owner){
                Log::error($user["user_uid"].'私有文章，判断权限'.$id);
                //非所有者
                if(CollectionController::UserCanRead($user['user_uid'],$result)===false){
                    Log::error($user["user_uid"].'没有读取权限');
                    return $this->error(__('auth.failed'),403,403);
                }
            }
        }
        $result->fullArticleList = true;
        return $this->ok(new CollectionResource($result));

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
        $collection  = Collection::find($id);
        if(!$collection){
            return $this->error("no recorder");
        }
        //鉴权
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'),401,401);
        }
        if(!CollectionController::UserCanEdit($user["user_uid"],$collection)){
            return $this->error(__('auth.failed'),403,403);
        }
        $collection->title = $request->get('title');
        $collection->subtitle = $request->get('subtitle');
        $collection->summary = $request->get('summary');
        if($request->has('aritcle_list')){
            $collection->article_list = \json_encode($request->get('aritcle_list'));
        }
        $collection->lang = $request->get('lang');
        $collection->status = $request->get('status');
        $collection->default_channel = $request->get('default_channel');
        $collection->modify_time = time()*1000;
        $collection->save();
        return $this->ok(new CollectionResource($collection));
    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,string $id)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        $collection = Collection::find($id);
        if($user['user_uid'] !== $collection['owner']){
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        DB::transaction(function() use($collection,$delete){
            //TODO 删除文集中的文章
            $delete = $collection->delete();
        });

        return $this->ok($delete);
    }
}
