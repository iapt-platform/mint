<?php

namespace App\Http\Controllers;

use App\Models\CourseMember;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Resources\CourseMemberResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\Log;

class CourseMemberController extends Controller
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
		$indexCol = ['id','user_id','course_id','role','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'course':
	            # 获取 course 内所有 成员
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //TODO 判断当前用户是否有指定的 course 的权限
                $table = CourseMember::where('course_id', $request->get('id'));
				break;
            case 'user':
                //获取某个用户的角色
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //TODO 判断当前用户是否有指定的 course 的权限
                $table = CourseMember::where('course_id', $request->get('course'))
                                    ->where('user_id', $user['user_uid']);
                break;
        }
        if(isset($_GET["search"])){
            $table = $table->where('title', 'like', $_GET["search"]."%");
        }
        $count = $table->count();
        if(isset($_GET["order"]) && isset($_GET["dir"])){
            $table = $table->orderBy($_GET["order"],$_GET["dir"]);
        }else{
            $table = $table->orderBy('updated_at','desc');
        }

        if(isset($_GET["limit"])){
            $offset = 0;
            if(isset($_GET["offset"])){
                $offset = $_GET["offset"];
            }
            $table = $table->skip($offset)->take($_GET["limit"]);
        }
        $result = $table->get();

        //获取当前用户角色
        $isOwner = Course::where('id',$request->get('id'))->where('studio_id',$user["user_uid"])->exists();
        $role = 'unknown';
        if($isOwner){
            $role = 'owner';
        }else{
            foreach ($result as $key => $value) {
            # 找到当前用户
            if($user["user_uid"]===$value->user_id){
                switch ($value->role) {
                    case 'assistant':
                        $role = 'manager';
                        break;
                    default:
                        # code...
                        break;
                }
                break;
            }
        }
        }

		if($result){
			return $this->ok(["rows"=>CourseMemberResource::collection($result),'role'=>$role,"count"=>$count]);
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
        $validated = $request->validate([
            'user_id' => 'required',
            'course_id' => 'required',
            'role' => 'required',
        ]);
        //查找重复的项目
        if(CourseMember::where('course_id', $validated['course_id'])
                      ->where('user_id',$validated['user_id'])
                      ->exists()){
            return $this->error('member exists');
        }
        $newMember = new CourseMember();
        $newMember->user_id = $validated['user_id'];
        $newMember->course_id = $validated['course_id'];
        $newMember->role = $validated['role'];
        /**
         * 查找course 信息，根据加入方式设置状态
         * open : accepted
         * manual: progressing
         */
        $course  = Course::find($validated['course_id']);
        if($course){
            switch ($course->join) {
                case 'open':
                    $newMember->status = 'accepted';
                    break;
                case 'manual':
                    $newMember->status = 'progressing';
                    break;
                default:
                    # code...
                    break;
            }
        }else{
            return $this->error('invalid course');
        }

        $newMember->save();

        return $this->ok(new CourseMemberResource($newMember));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CourseMember  $courseMember
     * @return \Illuminate\Http\Response
     */
    public function show(CourseMember $courseMember)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CourseMember  $courseMember
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CourseMember $courseMember)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        if($request->has('channel_id')) {
            if($courseMember->user_id !== $user['user_uid']){
                return $this->error(__('auth.failed'));
            }
            $courseMember->channel_id = $request->get('channel_id');
        }
        if($request->has('status')) {
            $courseMember->status = $request->get('status');
        }
        $courseMember->save();
        return $this->ok(new CourseMemberResource($courseMember));

    }
    public function set_channel(Request $request)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        if($request->has('channel_id')) {
            $courseMember = CourseMember::where('course_id',$request->get('course_id'))
                                        ->where('user_id',$user['user_uid'])
                                        ->first();
            if($courseMember){
                $courseMember->channel_id = $request->get('channel_id');
                $courseMember->save();
                return $this->ok(new CourseMemberResource($courseMember));
            }else{
                return $this->error(__('auth.failed'));
            }
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CourseMember  $courseMember
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,CourseMember $courseMember)
    {
        //查看删除者有没有删除权限
        //查询删除者的权限
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        $isOwner = Course::where('id',$courseMember->course_id)->where('studio_id',$user["user_uid"])->exists();
        if(!$isOwner){
            $courseUser = CourseMember::where('course_id',$courseMember->course_id)
                ->where('user_id',$user["user_uid"])
                ->select('role')->first();
            //open 课程 可以删除自己

           if(!$courseUser || $courseUser->role ==="student"){
                //普通成员没有删除权限
                return $this->error(__('auth.failed'));
            }
        }

        $delete = $courseMember->delete();
        return $this->ok($delete);
    }

    /**
     * 获取当前用户权限
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function curr(Request $request)
    {
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $courseUser = CourseMember::where('course_id',$request->get("course_id"))
                ->where('user_id',$user["user_uid"])
                ->select(['role','channel_id'])->first();
        if($courseUser){
            return $this->ok($courseUser);
        }else{
            return $this->error("not member");
        }
    }
}
