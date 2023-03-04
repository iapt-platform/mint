<?php

namespace App\Http\Controllers;

use App\Models\GroupInfo;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;


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
                $user = AuthApi::current($request);
                if($user){
                    //判断当前用户是否有指定的studio的权限
                    if($user['user_uid'] === StudioApi::getIdByName($request->get('name'))){
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if($user['user_uid'] !== StudioApi::getIdByName($request->get('studio_name'))){
            return $this->error(__('auth.failed'));
        }
        //查询是否重复
        if(GroupInfo::where('name',$request->get('name'))->where('owner',$user['user_uid'])->exists()){
            return $this->error(__('validation.exists',['name']));
        }

        $group = new GroupInfo;
        $group->id = app('snowflake')->id();
        $group->uid = Str::uuid();
        $group->name = $request->get('name');
        $group->owner = $user['user_uid'];
        $group->create_time = time()*1000;
        $group->modify_time = time()*1000;
        $group->save();
        return $this->ok($group);
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
		if(!$result){
            return $this->error("没有查询到数据");
		}
        if($result->status<30){
            //私有，判断权限
            $user = AuthApi::current($request);
            if(!$user){
                return $this->error(__('auth.failed'));
            }
            //判断当前用户是否有指定的studio的权限
            if($user['user_uid'] !== $result->owner){
                //非所有者
                //TODO 判断是否协作
                return $this->error(__('auth.failed'));
            }
        }
        return $this->ok($result);
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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if($user['user_uid'] !== StudioApi::getIdByName($request->get('studio'))){
            return $this->error(__('auth.failed'));
        }
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        $group->status = $request->get('status');
        $group->create_time = time()*1000;
        $group->modify_time = time()*1000;
        $group->save();
        return $this->ok($group);
    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupInfo  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,GroupInfo $group)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的 group 的删除权限
        if($user['user_uid'] !== $group->owner){
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        DB::transaction(function() use($group,$delete){
            //删除group member
            $memberDelete = GroupMember::where('group_id',$group->uid)->delete();
            $delete = $group->delete();
        });

        return $this->ok($delete);
    }
}
