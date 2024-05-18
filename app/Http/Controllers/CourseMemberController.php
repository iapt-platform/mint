<?php

namespace App\Http\Controllers;

use App\Models\CourseMember;
use App\Models\Course;
use App\Models\UserInfo;

use Illuminate\Http\Request;
use App\Http\Resources\CourseMemberResource;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\Log;
use App\Http\Api\UserApi;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed',[403],403));
        }
        //判断当前用户是否有指定的 course 的权限
        $role = CourseMember::where('course_id', $request->get('id',$request->get('course')))
                            ->where('user_id',$user['user_uid'])
                            ->value('role');
        if(empty($role)){
            return $this->error(__('auth.failed',[403],403));
        }

        $result=false;
		$indexCol = ['id','user_id','course_id',
                    'channel_id','role','editor_uid',
                    'updated_at','created_at'];
		switch ($request->get('view')) {
            case 'course':
	            # 获取 course 内所有 成员
                $table = CourseMember::where('course_id', $request->get('id'))
                                    ->where('is_current',true);
				break;
            case 'timeline':
                /**
                 * 编辑时间线
                 */
                $table = CourseMember::where('user_id',$request->get('userId'));
                if($request->get('timeline','current')==='current'){
                    $table = $table->where('course_id', $request->get('course'));
                }

                break;
            default:
                return $this->error('无法识别的参数view',400,400);
            break;
        }
        if(!empty($request->get("search"))){
            $usersId = UserInfo::where('nickname','like', '%'.$request->get("search")."%")
                            ->select('userid')
                            ->get();
            $table = $table->whereIn('user_id', $usersId);
        }

        $count = $table->count();

        $table = $table->orderBy($request->get('order','created_at'),
                                $request->get('dir','asc'));

        $table = $table->skip($request->get('offset',0))
              ->take($request->get('limit',1000));

        $result = $table->get();

        //获取当前用户角色
        $role = CourseMember::where('course_id', $request->get('id'))
                            ->where('user_id', $user['user_uid'])
                            ->where('is_current',true)
                            ->value('role');

		return $this->ok(["rows"=>CourseMemberResource::collection($result),'role'=>$role,"count"=>$count]);

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
            return $this->error(__('auth.failed',[403],403));
        }
        $validated = $request->validate([
            'user_id' => 'required',
            'course_id' => 'required',
            'role' => 'required',
            'status' => 'required',
        ]);
        //查找重复的
        if($validated['status'] !== 'invited'){
            if(CourseMember::where('course_id', $validated['course_id'])
                        ->where('user_id',$validated['user_id'])
                        ->exists()){
                return $this->error('member exists',[200],200);
            }
        }

        if($validated['status'] === 'invited'){
            $userId = $validated['user_id'];
        }else{
            $userId = $user['user_uid'];
        }

        CourseMember::where('course_id',$validated['course_id'])
            ->where('user_id',$userId)
            ->update(['is_current'=>false]);

        $newMember = new CourseMember();
        $newMember->course_id = $validated['course_id'];
        $newMember->role = $validated['role'];
        $newMember->editor_uid = $user['user_uid'];
        $newMember->status = $validated['status'];
        $newMember->user_id = $userId;

        /**
         * 查找course 信息，根据加入方式设置状态
         * open : accepted
         * manual: progressing
         */
        $course  = Course::find($validated['course_id']);
        if(!$course){
            return $this->error('invalid course');
        }
        switch ($course->join) {
            case 'open': //开放学习课程
                if($validated['status']!=='joined' &&
                    $validated['status']!=='invited'
                    ){
                    return $this->error('invalid course',[200],200);
                    }
                break;
            case 'manual': //人工审核课程
                if($validated['status']!=='applied' &&
                    $validated['status']!=='invited'
                    ){
                    return $this->error('invalid course',[200],200);
                    }
                break;
        }
        $newMember->save();

        return $this->ok(new CourseMemberResource($newMember));

    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $courseId
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,string $courseId)
    {
        //
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }
        $userId = $user['user_uid'];
        if(!empty($request->get('user_uid'))){
            $userId = $request->get('user_uid');
        }
        $member = CourseMember::where('course_id',$courseId)
                                ->where('user_id',$userId)
                                ->where('is_current',true)
                                ->first();
        if($member){
            return $this->ok(new CourseMemberResource($member));
        }else{
            return $this->error('no result',200,200);
        }
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
        /**
         * 保留原有记录
         * 增加一条新纪录
         * 原有记录变为历史记录
         */
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error(__('auth.failed'));
        }

        $newMember = new CourseMember();
        $newMember->user_id = $courseMember->user_id;
        $newMember->course_id = $courseMember->course_id;
        $newMember->role = $courseMember->role;
        $newMember->status = $courseMember->status;
        $newMember->channel_id = $courseMember->channel_id;
        $newMember->editor_uid = $user['user_uid'];

        $courseMember->is_current = false;
        $courseMember->save();

        if($request->has('channel_id')) {
            if($newMember->user_id !== $user['user_uid']){
                return $this->error(__('auth.failed'));
            }
            $newMember->channel_id = $request->get('channel_id');
        }
        if($request->has('status')) {
            $newMember->status = $request->get('status');
        }
        $newMember->save();
        return $this->ok(new CourseMemberResource($newMember));

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

            if(!$courseUser){
                //被删除的不是自己
                if($courseUser->role ==="student"){
                    //普通成员没有删除权限
                    return $this->error(__('auth.failed'));
                }
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
                                ->where('is_current',true)
                                ->select(['role','channel_id'])->first();
        if($courseUser){
            return $this->ok($courseUser);
        }else{
            return $this->error("not member");
        }
    }

    public function export(Request $request){

        $courseUser = CourseMember::where('course_id',$request->get("course_id"))
                                    ->where('is_current',true)
                                    ->get();

        $spreadsheet = new Spreadsheet();
        $activeWorksheet = $spreadsheet->getActiveSheet();
        $activeWorksheet->setCellValue('A1', 'nickname');
        $activeWorksheet->setCellValue('B1', 'username');
        $activeWorksheet->setCellValue('C1', 'role');
        $activeWorksheet->setCellValue('D1', 'status');
        $activeWorksheet->setCellValue('E1', 'created_at');

        $currLine = 2;
        foreach ($courseUser as $key => $row) {
            $user = UserApi::getByUuid($row->user_id);
            $activeWorksheet->setCellValue("A{$currLine}", $user['nickName']);
            $activeWorksheet->setCellValue("B{$currLine}", $user['userName']);
            $activeWorksheet->setCellValue("C{$currLine}", $row->role);
            $activeWorksheet->setCellValue("D{$currLine}", $row->status);
            $activeWorksheet->setCellValue("E{$currLine}", $row->created_at);
            $currLine++;
        }
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="course_member.xlsx"');
        $writer->save("php://output");
    }
}
