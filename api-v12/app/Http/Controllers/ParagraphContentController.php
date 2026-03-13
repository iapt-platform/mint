<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Sentence;
use App\Models\PaliText;
use Illuminate\Support\Str;
use App\Http\Api\ChannelApi;




use App\Services\PaliContentService;

class ParagraphContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *      * @param  \Illuminate\Http\Request  $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, string $id)
    {
        $paliService = app(PaliContentService::class);

        //
        $sentId = \explode('-', $id);
        $channels = [];
        if ($request->has('channels')) {
            if (strpos($request->get('channels'), ',') === FALSE) {
                $_channels = explode('_', $request->get('channels'));
            } else {
                $_channels = explode(',', $request->get('channels'));
            }
            foreach ($_channels as $key => $channel) {
                if (Str::isUuid($channel)) {
                    $channels[] = $channel;
                }
            }
        }

        $mode = $request->get('mode', 'read');
        if ($mode === 'read') {
            //阅读模式加载html格式原文
            $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
        } else {
            //翻译模式加载json格式原文
            $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
        }

        if ($channelId !== false) {
            $channels[] = $channelId;
        }

        $chapter = PaliText::where('book', $sentId[0])->where('paragraph', $sentId[1])->first();
        if (!$chapter) {
            return $this->error("no data");
        }

        #获取channel索引表

        $indexChannel = [];
        $indexChannel = $paliService->getChannelIndex($channels);
        $from = $sentId[1];
        $to =  $sentId[2] ?? $sentId[1];
        $record = Sentence::where('book_id', $sentId[0])
            ->whereBetween('paragraph', [$from, $to])
            ->whereIn('channel_uid', $channels)
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();
        if (count($record) === 0) {
            return $this->error("no data");
        }
        $result = [];
        $result['content'] = json_encode($paliService->makeContentObj($record, $mode, $indexChannel), JSON_UNESCAPED_UNICODE);
        $result['content_type'] = 'json';
        return $this->ok($result);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
