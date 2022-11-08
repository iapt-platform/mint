<?php

namespace App\Http\Controllers;

use App\Models\GroupInfo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

require_once __DIR__.'/../../../public/app/ucenter/function.php';
class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $userinfo = new \UserInfo();
		$result=false;
		$indexCol = ['uid','name','description','owner','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'studio':
	            # 获取studio内所有channel
                $user = \App\Http\Api\AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] === \App\Http\Api\StudioApi::getIdByName($request->get('name'))){
                        $table = GroupInfo::select($indexCol)->where('owner', $user["user_uid"]);
                    }else{
                        return $this->error(__('auth.failed'));
                    }
                }else{
                    return $this->error(__('auth.failed'));
                }
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
                $value->role = 'owner';
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request  $request,$id)
    {
        //
		$indexCol = ['uid','name','description','owner','updated_at','created_at'];

		$result  = GroupInfo::select($indexCol)->where('uid', $id)->first();
		if($result){
            if($result->status<30){
                //私有，判断权限
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
			return $this->ok($result);
		}else{
			return $this->error("没有查询到数据");
		}
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        //
    }
}
