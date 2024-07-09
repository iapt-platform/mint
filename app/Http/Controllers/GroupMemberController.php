<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use App\Models\GroupInfo;
use Illuminate\Http\Request;
use App\Http\Resources\GroupMemberResource;
use App\Http\Api\AuthApi;

class GroupMemberController extends Controller
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
		$indexCol = ['id','user_id','group_id','power','level','status','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'group':
	            # 获取 group 内所有 成员
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                    //判断当前用户是否有指定的 group 的权限
                    if(GroupMember::where('group_id', $request->get('id'))
                            ->where('user_id',$user['user_uid'])
                            ->exists()){
                                $table = GroupMember::where('group_id', $request->get('id'));
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
            $table = $table->orderBy('created_at');
        }

        $table->skip($request->get('offset',0))
              ->take($request->get('limit',1000));

        $result = $table->get();

        //当前用户角色
        $power = GroupMember::where('group_id', $request->get('id'))
                            ->where('user_id',$user['user_uid'])
                            ->value('power');
        switch ($power) {
            case 0:
                $role = "owner";
                break;
            case 1:
                $role = "manager";
                break;
            case 2:
                $role = "member";
                break;
            default:
                $role="unknown";
                break;
        }

        return $this->ok([
            "rows"=>GroupMemberResource::collection($result),
            "count"=>$count,
            'role'=>$role
        ]);
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
            'user_id' => 'required',
            'group_id' => 'required',
        ]);
        //查找重复的项目
        if(GroupMember::where('group_id', $validated['group_id'])->where('user_id',$validated['user_id'])->exists()){
            return $this->error('member exists');
        }
        $newMember = new GroupMember();
        $newMember->id=app('snowflake')->id();
        $newMember->user_id = $validated['user_id'];
        $newMember->group_id = $validated['group_id'];
        $newMember->power = 2;
        $newMember->group_name = GroupInfo::find($validated['group_id'])->name;
        $newMember->save();
        return $this->ok(new GroupMemberResource($newMember));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\GroupMember  $groupMember
     * @return \Illuminate\Http\Response
     */
    public function show(GroupMember $groupMember)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupMember  $groupMember
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GroupMember $groupMember)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *@param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupMember  $groupMember
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, GroupMember $groupMember)
    {
        //
        //查看删除者有没有删除权限
        //查询删除者的权限
        $currUser = AuthApi::current($request);
        if(!$currUser){
            return $this->error(__('auth.failed'));
        }

        $power = GroupMember::where('group_id',$groupMember->group_id)
                        ->where('user_id',$currUser["user_uid"])
                        ->select('power')->first();
        if(!$power || $power->power>=2){
            //普通成员没有删除权限
            return $this->error(__('auth.failed'));
        }

        $delete = $groupMember->delete();
        return $this->ok($delete);

    }
}
