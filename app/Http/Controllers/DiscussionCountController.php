<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\ChannelApi;
use App\Http\Resources\DiscussionCountResource;
use App\Http\Resources\TagMapResource;
use Illuminate\Support\Facades\Log;
use App\Models\Discussion;
use App\Models\CourseMember;
use App\Models\Course;
use App\Models\Sentence;
use App\Models\WbwBlock;
use App\Models\Wbw;
use App\Models\TagMap;



class DiscussionCountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * 课程模式业务逻辑
     * 标准答案channel:学生提问
     * 学生channel: 老师批改作业
     * 老师：
     *   标准答案channel: 本期学生，老师（区分已经回复，未回复）
     *   学生channel: 本期学生，老师
     * 学生：
     *   标准答案channel：我自己topic(区分已经回复，未回复)
     *   学生自己channel: 我自己，本期老师
     *
     * 输入：
     *   句子列表
     *   courseId
     * 返回数据
     *  resId
     *  type:'discussion':
     *     my:number
     *     myReplied:number
     *     all:number
     *     allReplied:number
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * 课程
         * 1. 获取用户角色
         * 2. 获取成员列表
         * 3. 计算答案channel的结果
         * 4. 计算作业channel的结果
         */
        $user = AuthApi::current($request);
        if(!$user){
            return $this->error('auth.failed',401,401);
        }
        $studioIdForTag = $user["user_uid"];
        if($request->has('course_id')){
            //判断我的角色
            $my = CourseMember::where('user_id',$user["user_uid"])
                                ->where('is_current',true)
                                ->where('course_id',$request->get('course_id'))
                                ->first();
            if(!$my){
                return $this->error('auth.failed',403,403);
            }
            //获取全部成员列表
            $allMembers = CourseMember::where('is_current',true)
                                ->where('course_id',$request->get('course_id'))
                                ->select('user_id')
                                ->get();
            Log::debug('allMembers',['members'=>$allMembers]);
            //找到全部相关channel
            $channels = array();
            //获取答案 channel
            $answerChannel = Course::where('id',$request->get('course_id'))
                            ->value('channel_id');
            $exerciseChannels = CourseMember::where('is_current',true)
                                    ->where('course_id',$request->get('course_id'))
                                    ->select('channel_id')
                                    ->get();
            if($answerChannel){
                array_push($channels,$answerChannel);
            }
            $users = array();
            if($my->role === 'student'){
                //自己的channel + 答案
                if($my->channel_id){
                    array_push($channels,$my->channel_id);
                }
            }else{
                //找到全部学员channel + 答案

                foreach ($exerciseChannels as $key => $value) {
                    array_push($channels,$value->channel_id);
                }
                //找到
                $courseStudioId = Course::where('id',$request->get('course_id'))
                            ->value('studio_id');
                if($courseStudioId){
                    $studioIdForTag = $courseStudioId;
                }

            }
        }

        //获取全部资源列表
        $resId = array();
        $querySentId = $request->get('sentences');
        //译文
        $table = Sentence::select('uid')
                        ->whereIns(['book_id','paragraph','word_start','word_end'],$querySentId);
        if(isset($channels)){
            $table = $table->whereIn('channel_uid',$channels);
        }
        $sentUid = $table->get();

        foreach ($sentUid as $key => $value) {
            $resId[] = $value->uid;
        }
        //wbw
        $wbwBlockParagraphs = [];
        foreach ($querySentId as $key => $value) {
            $wbwBlockParagraphs[] = [$value[0],$value[1]];
        }
        $table = WbwBlock::select('uid')
                          ->whereIns(['book_id','paragraph'],$wbwBlockParagraphs);
        if(isset($channels)){
            $table = $table->whereIn('channel_uid',$channels);
        }
        $wbwBlock = $table->get();
        if($wbwBlock){
            //找到逐词解析数据
            foreach ($querySentId as $key => $value) {
                $wbwData = Wbw::whereIn('block_uid',$wbwBlock)
                                ->whereBetween('wid',[$value[2],$value[3]])
                                ->select('uid')
                                ->get();
                foreach ($wbwData as $key => $value) {
                    $resId[] = $value->uid;
                }
            }
        }
        Log::debug('res id',['res'=>$resId]);
        //全部资源id获取完毕
        //获取discussion
        $table = Discussion::select(['id','res_id','res_type','type','editor_uid'])
                            ->where('status','active')
                            ->whereNull('parent')
                            ->whereIn('res_id',$resId);
        if(isset($allMembers)){
            $table = $table->whereIn('editor_uid',$allMembers);
        }

        $allDiscussions = $table->get();
        $discussions = DiscussionCountResource::collection($allDiscussions);

        //获取 tag
        $tags = TagMap::select(['tag_maps.id','anchor_id','table_name','tag_id','editor_uid','tags.name','tags.color'])
                            ->whereIn('anchor_id',$resId)
                            ->where('owner_uid',$studioIdForTag)
                            ->leftJoin('tags','tags.id', '=', 'tag_maps.tag_id')
                            ->get();
        Log::debug('response',['data'=>$discussions]);
        return $this->ok([
            'discussions'=>$discussions,
            'tags' => $tags,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $resId
     * @return \Illuminate\Http\Response
     */
    public function show(string  $resId)
    {
        //
        $allDiscussions = Discussion::where('status','active')
                                    ->whereNull('parent')
                                    ->where('res_id',$resId)
                                    ->select(['id','res_id','res_type','type','editor_uid'])
                                    ->get();
        $discussions = DiscussionCountResource::collection($allDiscussions);

        //获取 tag
        $table = TagMap::select(['id','anchor_id','table_name','tag_id','editor_uid'])
                       ->where('anchor_id',$resId);

        $allTags = $table->get();
        $tags = TagMapResource::collection($allTags);
        Log::debug('response',['discussions'=>$discussions]);
        return $this->ok([
            'discussions'=>$discussions,
            'tags' => $tags,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Discussion $discussion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discussion  $discussion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Discussion $discussion)
    {
        //
    }
}
