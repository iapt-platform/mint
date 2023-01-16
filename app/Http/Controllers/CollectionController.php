<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/../../../public/app/ucenter/function.php';


class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
                //
        $userinfo = new \UserInfo();
		$result=false;
		$indexCol = ['uid','title','subtitle','summary','article_list','owner','status','lang','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'studio_list':
		        $indexCol = ['owner'];
                $table = Collection::select($indexCol)->selectRaw('count(*) as count')->where('status', 30)->groupBy('owner');
                break;
			case 'studio':
				# code...
				//$table = Collection::select($indexCol)->where('owner', $_COOKIE["user_uid"]);
                # 获取studio内所有channel
                $user = \App\Http\Api\AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] === \App\Http\Api\StudioApi::getIdByName($request->get('name'))){
                        $table = Collection::select($indexCol)->where('owner', $user["user_uid"]);
                    }else{
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
				break;
			case 'public':
				$table = Collection::select($indexCol)->where('status', 30);
				break;
            case 'public_studio':
                $user = $userinfo->getUserByName($request->get('studio'));
                $table = Collection::select($indexCol)->where('status', 30)->where('owner',$user['userid']);
                break;
			default:
				# code...
			    return $this->error("没有查询到数据");
				break;
		}
        if(isset($_GET["search"])){
            $table = $table->where('title', 'like', $_GET["search"]."%");
        }
        $count = $table->count();
        if(isset($_GET["order"]) && isset($_GET["dir"])){
            $table = $table->orderBy($_GET["order"],$_GET["dir"]);
        }else{
            if($request->get('view') === 'studio_list'){
                $table = $table->orderBy('count','desc');
            }else{
                $table = $table->orderBy('updated_at','desc');
            }
        }

        if(isset($_GET["limit"])){
            $offset = 0;
            if(isset($_GET["offset"])){
                $offset = $_GET["offset"];
            }
            $table = $table->skip($offset)->take($_GET["limit"]);
        }
        $result = $table->get();
		if($result){
            foreach ($result as $key => $value) {
                # code...
                if(is_array(\json_decode($value->article_list))){
                    $value->childrenNumber = count(\json_decode($value->article_list));
                }else{
                    $value->childrenNumber = 0;
                }

                if(isset($value->article_list)){
                    $result[$key]->article_list = array_slice(\json_decode($value->article_list),0,4);
                }
                $value->studio = [
                    'id'=>$value->owner,
                    'nickName'=>$userinfo->getName($value->owner)['nickname'],
                    'studioName'=>$userinfo->getName($value->owner)['username'],
                    'avastar'=>'',
                    'owner' => [
                        'id'=>$value->owner,
                        'nickName'=>$userinfo->getName($value->owner)['nickname'],
                        'userName'=>$userinfo->getName($value->owner)['username'],
                        'avastar'=>'',
                    ]
                ];
            }
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
        $user = \App\Http\Api\AuthApi::current($request);
        if($user){
            //判断当前用户是否有指定的studio的权限
            if($user['user_uid'] === \App\Http\Api\StudioApi::getIdByName($request->get('studio'))){
                //查询是否重复
                if(Collection::where('title',$request->get('title'))->where('owner',$user['user_uid'])->exists()){
                    return $this->error(__('validation.exists'));
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
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request,$id)
    {
        //
		$indexCol = ['uid','title','subtitle','summary','article_list','owner','lang','updated_at','created_at'];

		$result  = Collection::select($indexCol)->where('uid', $id)->first();
		if($result){
            if($result->status<30){
                //私有文章，判断权限
                $user = \App\Http\Api\AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] !== $result->owner){
                        //非所有者
                        //TODO 判断是否协作
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
            }
			if(!empty($result->article_list)){
				$result->article_list = \json_decode($result->article_list);
			}
			return $this->ok($result);
		}else{
			return $this->error("没有查询到数据");
		}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $collection  = Collection::where('uid', $id)->first();
        if($collection){
            //鉴权
            Log::info("找到文集");
            $user = \App\Http\Api\AuthApi::current($request);
            if($user && $collection->owner === $user["user_uid"]){
                $collection->title = $request->get('title');
                $collection->subtitle = $request->get('subtitle');
                $collection->summary = $request->get('summary');
                $collection->article_list = \json_encode($request->get('aritcle_list')) ;
                $collection->lang = $request->get('lang');
                $collection->status = $request->get('status');
                $collection->modify_time = time()*1000;
                $collection->save();
                return $this->ok($collection);
            }else{
                //鉴权失败
                Log::info("鉴权失败");

                //TODO 判断是否为协作
                return $this->error(__('auth.failed'));
            }

        }else{
            return $this->error("no recorder");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collection  $collection
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collection $collection)
    {
        //
    }
}
