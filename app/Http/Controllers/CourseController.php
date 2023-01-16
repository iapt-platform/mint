<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMember;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;

class CourseController extends Controller
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
		$indexCol = ['id','title','subtitle','cover','content','content_type','teacher','start_at','end_at','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'create':
	            # 获取 studio 建立的所有 course
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== StudioApi::getIdByName($request->get('name'))){
                    return $this->error(__('auth.failed'));
                }

                $table = Course::where('studio_id', $user["user_uid"]);
				break;
            case 'study':
                //我学习的课程
                $course = CourseMember::where('user_id',$user["user_uid"])
                                      ->where('role','member')
                                      ->select('course_id')
                                      ->get();
                $courseId = [];
                foreach ($course as $key => $value) {
                    # code...
                    $courseId[] = $value->course_id;
                }
                $table = Course::whereIn('id', $courseId);
                break;
        }
        $table = $table->select($indexCol);
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
        if($user['user_uid'] !== StudioApi::getIdByName($request->get('studio'))){
            return $this->error(__('auth.failed'));
        }
        //查询是否重复
        if(Course::where('title',$request->get('title'))->where('studio_id',$user['user_uid'])->exists()){
            return $this->error(__('validation.exists',['name']));
        }

        $course = new Course;
        $course->title = $request->get('title');
        $course->studio_id = $user['user_uid'];
        $course->save();
        return $this->ok($course);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        //
        return $this->ok($course);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
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
        //查询标题是否重复
        if(Course::where('title',$request->get('title'))->where('studio_id',$user['user_uid'])->exists()){
            return $this->error(__('validation.exists',['name']));
        }
        $course->title = $request->get('title');
        $course->subtitle = $request->get('subtitle');
        $course->cover = $request->get('cover');
        $course->content = $request->get('content');
        $course->teacher = $request->get('teacher');
        $course->start_at = $request->get('start_at');
        $course->end_at = $request->get('end_at');
        $course->save();
        return $this->ok($course);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
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
        $delete = 0;
        DB::transaction(function() use($delete){
            //删除group member
            $memberDelete = CourseMember::where('course_id',$course->id)->delete();
            $delete = $course->delete();
        });

        $this->ok($delete);
    }
}
