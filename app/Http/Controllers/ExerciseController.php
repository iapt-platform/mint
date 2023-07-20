<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseMember;
use App\Models\Article;
use App\Models\WbwBlock;
use App\Models\Wbw;
use App\Models\Discussion;
use App\Models\Sentence;
use Illuminate\Http\Request;
use App\Http\Api\MDRender;
use App\Http\Api\UserApi;

class ExerciseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /**
         * 列出某个练习所有人的提交情况
         * 情况包括
         * 1.作业填充百分比
         * 2.问题数量
         */
        $validated = $request->validate([
            'course_id' => 'required',
            'article_id' => 'required',
            'exercise_id' => 'required',
        ]);
        $output = [];
        //课程信息
        $course = Course::findOrFail($validated['course_id']);

        //查询练习句子编号
        $article = Article::where('uid',$validated['article_id'])->value('content');

        $wiki = MdRender::markdown2wiki($article);
        $xml = MdRender::wiki2xml($wiki);
        $html = MdRender::xmlQueryId($xml, $validated['exercise_id']);
        $sentences = MdRender::take_sentence($html);

        //获取课程答案逐词解析列表
        $answerWbw = [];
        foreach ($sentences as  $sent) {
            # code...wbw
            $sentId = explode('-',$sent);
            if(count($sentId)<4){
                break;
            }
            $courseWb = WbwBlock::where('book_id',$sentId[0])
                            ->where('paragraph',$sentId[1])
                            ->where('channel_uid',$course->channel_id)
                            ->value('uid');
            if($courseWb){
                $wbwId = Wbw::where('block_uid',$courseWb)
                    ->whereBetween('wid',[$sentId[2],$sentId[3]])
                    ->select('uid')->get();
                foreach ($wbwId as $id) {
                    # code...
                    $answerWbw[] = $id->uid;
                }
            }
        }
        $members = CourseMember::where('course_id',$validated['course_id'])
                            ->where('role','student')
                            ->select(['user_id','channel_id'])
                            ->get();
        foreach ($members as  $member) {
            # code...
            $data = [
                'user' => UserApi::getByUuid($member->user_id),
                'wbw' => 0,
                'translation' => 0,
                'question' => 0,
                'html' => ""
            ];
            if(!empty($member->channel_id)){
                //
                foreach ($sentences as  $sent) {
                    # code...wbw
                    $sentId = explode('-',$sent);
                    if(count($sentId)<4){
                        break;
                    }
                    $wb = WbwBlock::where('book_id',$sentId[0])
                            ->where('paragraph',$sentId[1])
                            ->where('channel_uid',$member->channel_id)
                            ->value('uid');
                    if($wb){
                        $wbwCount = Wbw::where('block_uid',$wb)
                            ->whereBetween('wid',[$sentId[2],$sentId[3]])
                            ->where('status','>',4)
                            ->count();
                        $data['wbw'] += $wbwCount;
                    }
                    //translation
                    $sentCount = Sentence::where('book_id',$sentId[0])
                            ->where('paragraph',$sentId[1])
                            ->where('word_start',$sentId[2])
                            ->where('word_end',$sentId[3])
                            ->where('channel_uid',$member->channel_id)
                            ->count();
                    $data['translation'] += $sentCount;
                    //discussion
                    //查找答案的wbw 对应的discussion
                    $discussionCount = Discussion::whereIn('res_id',$answerWbw)
                            ->where('editor_uid',$member->user_id)
                            ->whereNull('parent')
                            ->count();
                    $data['question'] += $discussionCount;

                    $tpl = MdRender::xml2tpl($html,$member->channel_id);
                    $data['html'] .= $tpl;
                }
            }
            $output[] = $data;
        }
        return $this->ok(["rows"=>$output,"count"=>count($output)]);
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
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        //
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
    }
}
