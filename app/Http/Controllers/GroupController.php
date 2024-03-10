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
use App\Http\Resources\GroupResource;


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
		$result=false;
		$indexCol = ['uid','name','description','owner','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'studio':
	            # 获取studio内所有group
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                $studioId = StudioApi::getIdByName($request->get('name'));
                if($user['user_uid'] !== $studioId){
                    return $this->error(__('auth.failed'));
                }

                $table = GroupInfo::select($indexCol);
                if($request->get('view2','my')==='my'){
                    $table = $table->where('owner', $studioId);
                }else{
                    //我参加的group
                    $groupId = GroupMember::where('user_id',$studioId)
                                          ->groupBy('group_id')
                                          ->select('group_id')
                                          ->get();
                    $table = $table->whereIn('uid', $groupId);
                    $table = $table->where('owner','<>', $studioId);
                }
				break;
            case 'all':
                $table = GroupInfo::select($indexCol);
                break;
        }
        if($request->has("search")){
            $table = $table->where('name', 'like', "%" . $request->get("search")."%");
        }
        $count = $table->count();

        if($request->get('view') === 'studio_list'){
            $table = $table->orderBy('count','desc');
        }else{
            $table = $table->orderBy($request->get('order','updated_at'),
                                        $request->get('dir','desc'));
        }
        $table->skip($request->get('offset',0))
              ->take($request->get('limit',1000));

        $result = $table->get();
		if($result){
			return $this->ok(["rows"=>GroupResource::collection($result),"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
		}

    }
    /**
     * 获取我的，和协作channel数量
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
        $my = GroupMember::where('user_id', $studioId)->where('power',0)->count();
        //协作
        $collaboration = GroupMember::where('user_id', $studioId)->where('power','<>',0)->count();

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
        $studioId = StudioApi::getIdByName($request->get('studio_name'));
        $group = new GroupInfo;
        DB::transaction(function() use($group,$request,$user,$studioId){
            $group->id = app('snowflake')->id();
            $group->uid = Str::uuid();
            $group->name = $request->get('name');
            $group->owner = $studioId;
            $group->create_time = time()*1000;
            $group->modify_time = time()*1000;
            $group->save();

            $newMember = new GroupMember();
            $newMember->id=app('snowflake')->id();
            $newMember->user_id = $studioId;
            $newMember->group_id = $group->uid;
            $newMember->power = 0;
            $newMember->group_name = $request->get('name');
            $newMember->save();
        });

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
        if($result->status < 30){
            //私有，判断权限
            $user = AuthApi::current($request);
            if(!$user){
                return $this->error(__('auth.failed'));
            }
            //判断当前用户是否有指定的group的权限
            if($user['user_uid'] !== $result->owner){
                //非所有者
                //判断是否协作
                $power = GroupMember::where('group_id', $id)
                            ->where('user_id',$user['user_uid'])
                            ->value('power');
                if($power === null){
                   return $this->error(__('auth.failed'));
                }
            }
        }
        return $this->ok(new GroupResource($result));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupInfo  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GroupInfo $group)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有修改权限
        if($user['user_uid'] !== $group->owner){
            return $this->error(__('auth.failed'));
        }
        $group->name = $request->get('name');
        $group->description = $request->get('description');
        if($request->has('status')) { $group->status = $request->get('status'); }
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
