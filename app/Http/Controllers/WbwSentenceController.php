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
        switch ($request->get('view')) {
            case 'sent-can-read':
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
                $channels = [];
                if($request->has('course')){
                    $studentsChannel = CourseMember::where('course_id',$request->get('course'))
                                                    ->whereNotNull('channel_id')
                                                    ->select('channel_id')
                                                    ->orderBy('created_at')
                                                    ->get();
                    foreach ($studentsChannel as $key => $channel) {
                        $channels[] = $channel->channel_id;
                    }
                    $channels[] = Course::where('id',$request->get('course'))
                                    ->value('channel_id');
                }else{
                    $channels = ChannelApi::getCanReadByUser($user_uid);
                }

                $channelsId = [];
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
                $wbwBlocksId = WbwBlock::where('book_id',$request->get('book'))
                                    ->where('paragraph',$request->get('para'))
                                    ->whereIn('channel_uid',$channelsId)
                                    ->select('uid')
                                    ->get();
                $validBlocks = Wbw::whereIn('block_uid',$wbwBlocksId)
                            ->where('book_id',$request->get('book'))
                            ->where('paragraph',$request->get('para'))
                            ->where('wid',$request->get('wordStart'))
                            ->select('block_uid')
                            ->groupBy('block_uid')
                            ->get();

                foreach ($validBlocks as $key => $block) {
                    $channel = WbwBlock::where('uid',$block->block_uid)->first();
                    $corpus = new CorpusController;
                    $props = $corpus->getSentTpl($sentId,[$channel->channel_uid],
                                               'edit',true,
                                               'react');
                    $result[] = $props;
                }
                return $this->ok(['rows'=>$result,'count'=>count($result)]);
                break;
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
