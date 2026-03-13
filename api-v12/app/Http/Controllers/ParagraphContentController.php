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
    public function index(Request $request, PaliContentService $paliService)
    {
        $data = $request->validate([
            'book' => 'required|integer',
            'para' => 'required|integer',
            'to' => 'integer',
        ]);

        $channels = [];
        if ($request->has('channels')) {
            $_channels = explode(',', str_replace('_', ',', $request->get('channels')));
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

        $chapter = PaliText::where('book', $data['book'])
            ->where('paragraph', $data['para'])->first();
        if (!$chapter) {
            return $this->error("no data");
        }

        #获取channel索引表

        $indexChannel = [];
        $indexChannel = $paliService->getChannelIndex($channels);
        $from = $data['para'];
        if (isset($data['to'])) {
            $to = $data['to'];
        } else {
            $to = $data['para'];
        }
        $record = Sentence::where('book_id', $data['book'])
            ->whereBetween('paragraph', [$from, $to])
            ->whereIn('channel_uid', $channels)
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();
        if (count($record) === 0) {
            return $this->error("no data");
        }
        $result = $paliService->makeContentObj($record, $mode, $indexChannel);

        return $this->ok([
            'items' => $result,
            'pagination' => [
                'page' => 1,
                'pageSize' => $to - $from + 1,
                'total' => $to - $from + 1
            ],
        ]);
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
    public function show(Request $request, string $id) {}

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
