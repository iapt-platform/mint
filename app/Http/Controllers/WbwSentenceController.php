<?php

namespace App\Http\Controllers;

use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Models\Channel;
use App\Models\CourseMember;
use App\Models\Course;

use Illuminate\Http\Request;
use App\Http\Api\AuthApi;
use App\Http\Api\ShareApi;
use App\Http\Api\ChannelApi;
use App\Http\Api\CourseApi;

class WbwSentenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $channelsId = [];
        $result = [];
        $user = AuthApi::current($request);
        $user_uid = null;
        if($user){
            $user_uid = $user['user_uid'];
        }
        $sentId = $request->get('book').'-'.
                    $request->get('para').'-'.
                    $request->get('wordStart').'-'.
                    $request->get('wordEnd');
        switch ($request->get('view')) {
            case 'course-answer':
                if($request->has('course')){
                    $channelsId[] = Course::where('id',$request->get('course'))
                                    ->value('channel_id');
                }
                break;
            case 'sent-can-read':
                $channels = [];
                if($request->has('course')){
                    $channels = CourseApi::getStudentChannels($request->get('course'));
                    $channels[] = Course::where('id',$request->get('course'))
                                    ->value('channel_id');
                }else{
                    $channels = ChannelApi::getCanReadByUser($user_uid);
                }


                if($request->has('exclude')){
                    //移除无需查询的channel
                    foreach ($channels as $key => $id) {
                        if($id !== $request->get('exclude')){
                            $channelsId[] = $id;
                        }
                    }
                }else if($request->has('channels')){
                    //仅列出指定的channel
                    $include = explode(',', $request->get('channels'));
                    foreach ($channels as $key => $id) {
                        if(in_array($id, $include)){
                            $channelsId[] = $id;
                        }
                    }
                }else{
                    $channelsId = $channels;
                }
                break;
        }

        $validBlocks = WbwSentenceController::getBlocksByChannels(
                $channelsId,
                $request->get('book'),
                $request->get('para'),
                $request->get('wordStart')
            );

        foreach ($validBlocks as $key => $blockId) {
        $channel = WbwBlock::where('uid',$blockId)->first();
        $corpus = new CorpusController;
        $props = $corpus->getSentTpl($sentId,[$channel->channel_uid],
                        'edit',true,
                        'react');
        $result[] = $props;
        }
        return $this->ok(['rows'=>$result,'count'=>count($result)]);


    }

    public static function getBlocksByChannels($channelsId,$book,$para,$wordId){
        $wbwBlocksId = WbwBlock::where('book_id',$book)
                            ->where('paragraph',$para)
                            ->whereIn('channel_uid',$channelsId)
                            ->select('uid')
                            ->get();
        $validBlocks = Wbw::whereIn('block_uid',$wbwBlocksId)
                        ->where('book_id',$book)
                        ->where('paragraph',$para)
                        ->where('wid',$wordId)
                        ->select('block_uid')
                        ->groupBy('block_uid')
                        ->get();
        $blocksId = [];
        foreach ($validBlocks as $key => $block) {
            $blocksId[] = $block->block_uid;
        }
        return $blocksId;
    }

    public static function getWbwIdByChannels($channelsId,$book,$para,$wordId){
        $validBlocks = WbwSentenceController::getBlocksByChannels($channelsId,$book,$para,$wordId);
        $wbwId = Wbw::whereIn('block_uid',$validBlocks)
                        ->where('book_id',$book)
                        ->where('paragraph',$para)
                        ->where('wid',$wordId)
                        ->select('uid')
                        ->get();
        $id = [];
        foreach ($wbwId as $key => $value) {
            $id[] = $value->uid;
        }
        return $id;
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
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function show(Wbw $wbw)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Wbw $wbw)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Wbw  $wbw
     * @return \Illuminate\Http\Response
     */
    public function destroy(Wbw $wbw)
    {
        //
    }
}
