<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMember;
use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\StudioApi;
use App\Http\Resources\CourseResource;
use Illuminate\Support\Facades\DB;

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
		$indexCol = ['id','title','subtitle','cover','content','content_type','teacher','start_at','end_at','publicity','updated_at','created_at'];
		switch ($request->get('view')) {
            case 'new':
                //最新公开课程列表
                $table = Course::where('publicity', 30);
                break;
            case 'open':
                /**
                 * 开放课程列表
                 * 开放规则：
                 * 1. 公开
                 * 2. 课程开始时间比现在时间晚
                 */
                $table = Course::where('publicity', 30)
                            ->whereDate('start_at',">",date("Y-m-d",strtotime("today")));
                break;
            case 'close':
                /**
                 * 已经关闭课程列表
                 * 判定规则：
                 * 1. 公开
                 * 2. 课程开始时间比现在时间早
                 */
                $table = Course::where('publicity', 30)
                        ->whereDate('start_at',"<=",date("Y-m-d",strtotime("today")));
                break;
            case 'create':
	            # 获取 studio 建立的所有 course
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //判断当前用户是否有指定的studio的权限
                if($user['user_uid'] !== StudioApi::getIdByName($request->get('studio'))){
                    return $this->error(__('auth.failed'));
                }

                $table = Course::where('studio_id', $user["user_uid"]);
				break;
            case 'study':
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                //我学习的课程
                $course = CourseMember::where('user_id',$user["user_uid"])
                                      ->where('role','student')
                                      ->select('course_id')
                                      ->get();
                $courseId = [];
                foreach ($course as $key => $value) {
                    # code...
                    $courseId[] = $value->course_id;
                }
                $table = Course::whereIn('id', $courseId);
                break;
            case 'teach':
                //我任教的课程
                $user = AuthApi::current($request);
                if(!$user){
                    return $this->error(__('auth.failed'));
                }
                $course = CourseMember::where('user_id',$user["user_uid"])
                ->where('role','assistant')
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
		if($result){
			return $this->ok(["rows"=>CourseResource::collection($result),"count"=>$count]);
		}else{
			return $this->error("没有查询到数据");
		}
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function showMyCourseNumber(Request $request){
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //我建立的课程
        $create = Course::where('studio_id', $user["user_uid"])->count();
        //我学习的课程
        $study = CourseMember::where('user_id',$user["user_uid"])
        ->where('role','student')
        ->count();
        //我任教的课程
        $teach = CourseMember::where('user_id',$user["user_uid"])
        ->where('role','assistant')
        ->count();
        return $this->ok(['create'=>$create,'teach'=>$teach,'study'=>$study]);
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
        $studio_id = StudioApi::getIdByName($request->get('studio'));
        if($user['user_uid'] !== $studio_id){
            return $this->error(__('auth.failed'));
        }
        //查询是否重复
        if(Course::where('title',$request->get('title'))->where('studio_id',$user['user_uid'])->exists()){
            return $this->error(__('validation.exists',['name']));
        }

        $course = new Course;
        $course->title = $request->get('title');
        $course->studio_id = $studio_id;
        $course->save();
        return $this->ok(new CourseResource($course));
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
        return $this->ok(new CourseResource($course));

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
        if($user['user_uid'] !== $course->studio_id){
            return $this->error(__('auth.failed'));
        }
        //查询标题是否重复
        if(Course::where('title',$request->get('title'))->where('studio_id',$user['user_uid'])->exists()){
            if($course->title !== $request->get('title')){
                return $this->error(__('validation.exists',['name']));
            }
        }
        $course->title = $request->get('title');
        $course->subtitle = $request->get('subtitle');
        $course->summary = $request->get('summary');
        if($request->has('cover')) {$course->cover = $request->get('cover');}
        $course->content = $request->get('content');
        if($request->has('teacher_id')) {$course->teacher = $request->get('teacher_id');}
        if($request->has('anthology_id')) {$course->anthology_id = $request->get('anthology_id');}
        $course->channel_id = $request->get('channel_id');
        if($request->has('publicity')) {$course->publicity = $request->get('publicity');}
        $course->start_at = $request->get('start_at');
        $course->end_at = $request->get('end_at');
        $course->join = $request->get('join');
        $course->request_exp = $request->get('request_exp');
        $course->save();
        return $this->ok($course);
    }

    /**
     * Remove the specified resource from storage.
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,Course $course)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        //判断当前用户是否有指定的studio的权限
        if($user['user_uid'] !== $course->studio_id){
            return $this->error(__('auth.failed'));
        }
        $delete = 0;
        DB::transaction(function() use($delete,$course){
            //删除group member
            $memberDelete = CourseMember::where('course_id',$course->id)->delete();
            $delete = $course->delete();
        });

        return $this->ok($delete);
    }
}
