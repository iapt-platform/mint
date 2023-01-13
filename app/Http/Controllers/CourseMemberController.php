<?php

namespace App\Http\Controllers;

use App\Models\CourseMember;
use Illuminate\Http\Request;

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
		$indexCol = ['id','user_id','group_id','power','level','status','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'course':
	            # 获取 course 内所有 成员
                $user = AuthApi::current($request);
                if($user){
                    //TODO 判断当前用户是否有指定的 course 的权限

                    if(Course::where('id',$request->get('id'))->where('studio_id',$user['user_uid'])->exists()){
                        $table = CourseMember::where('course_id', $request->get('id'));
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
        foreach ($result as $key => $value) {
            # 找到当前用户的权限
            if($user["user_uid"]===$value->user_id){
                switch ($value->power) {
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
            }
        }

		if($result){
			return $this->ok(["rows"=>GroupMemberResource::collection($result),"count"=>$count,'role'=>$role]);
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CourseMember  $courseMember
     * @return \Illuminate\Http\Response
     */
    public function destroy(CourseMember $courseMember)
    {
        //
    }
}
