<?php

namespace App\Http\Controllers;

use App\Http\Api\ChannelApi;
use App\Http\Api\CourseApi;
use App\Models\Course;
use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WbwSentenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        //
        $channelsId = [];
        $result = [];
        $user = AuthService::current($request);
        $user_uid = null;
        if ($user) {
            $user_uid = $user['user_uid'];
        }
        $sentId = $request->input('book').'-'.
            $request->input('para').'-'.
            $request->input('wordStart').'-'.
            $request->input('wordEnd');
        switch ($request->input('view')) {
            case 'course-answer':
                if ($request->has('course')) {
                    $channelsId[] = Course::where('id', $request->input('course'))
                        ->value('channel_id');
                }
                break;
            case 'sent-can-read':
                $channels = [];
                if ($request->has('course')) {
                    $channels = CourseApi::getStudentChannels($request->input('course'));
                    $channels[] = Course::where('id', $request->input('course'))
                        ->value('channel_id');
                } else {
                    $channels = ChannelApi::getCanReadByUser($user_uid);
                }

                if ($request->has('exclude')) {
                    // 移除无需查询的channel
                    foreach ($channels as $key => $id) {
                        if ($id !== $request->input('exclude')) {
                            $channelsId[] = $id;
                        }
                    }
                } elseif ($request->has('channels')) {
                    // 仅列出指定的channel
                    $include = explode(',', $request->input('channels'));
                    foreach ($channels as $key => $id) {
                        if (in_array($id, $include)) {
                            $channelsId[] = $id;
                        }
                    }
                } else {
                    $channelsId = $channels;
                }
                break;
        }

        $validBlocks = WbwSentenceController::getBlocksByChannels(
            $channelsId,
            $request->input('book'),
            $request->input('para'),
            $request->input('wordStart')
        );

        foreach ($validBlocks as $key => $blockId) {
            $channel = WbwBlock::where('uid', $blockId)->first();
            $corpus = new CorpusController;
            $props = $corpus->getSentTpl(
                $sentId,
                [$channel->channel_uid],
                'edit',
                true,
                'react'
            );
            $result[] = $props;
        }

        return $this->ok(['rows' => $result, 'count' => count($result)]);
    }

    public static function getBlocksByChannels($channelsId, $book, $para, $wordId)
    {
        $wbwBlocksId = WbwBlock::where('book_id', $book)
            ->where('paragraph', $para)
            ->whereIn('channel_uid', $channelsId)
            ->select('uid')
            ->get();
        $validBlocks = Wbw::whereIn('block_uid', $wbwBlocksId)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->where('wid', $wordId)
            ->select('block_uid')
            ->groupBy('block_uid')
            ->get();
        $blocksId = [];
        foreach ($validBlocks as $key => $block) {
            $blocksId[] = $block->block_uid;
        }

        return $blocksId;
    }

    public static function getWbwIdByChannels($channelsId, $book, $para, $wordId)
    {
        $validBlocks = WbwSentenceController::getBlocksByChannels($channelsId, $book, $para, $wordId);
        $wbwId = Wbw::whereIn('block_uid', $validBlocks)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->where('wid', $wordId)
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
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(Wbw $wbw)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, Wbw $wbw)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Wbw  $wbw
     * @return Response
     */
    public function destroy(Request $request, string $sentId)
    {
        //
        // 鉴权
        $user = AuthService::current($request);
        if (! $user) {
            // 未登录用户
            return $this->error(__('auth.failed'), 401, 401);
        }
        $channelId = $request->input('channel');
        if (! ChannelApi::canManageByUser($channelId, $user['user_uid'])) {
            return $this->error(__('auth.failed'), 403, 403);
        }
        $sent = explode('-', $sentId);
        $wbwBlockId = WbwBlock::where('book_id', $sent[0])
            ->where('paragraph', $sent[1])
            ->where('channel_uid', $channelId)
            ->value('uid');
        $delete = Wbw::where('block_uid', $wbwBlockId)
            ->whereBetween('wid', [$sent[2], $sent[3]])
            ->delete();

        return $this->ok($delete);
    }
}
