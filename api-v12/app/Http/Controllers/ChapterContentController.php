<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Sentence;
use App\Models\Channel;
use App\Models\PaliText;

use Illuminate\Support\Str;
use App\Http\Api\MdRender;
use App\Http\Api\ChannelApi;
use App\Http\Api\StudioApi;


use App\Http\Api\AuthApi;
use App\Http\Resources\TocResource;
use App\Services\PaliContentService;

class ChapterContentController extends Controller
{
    protected $result = [
        "uid" => '',
        "title" => '',
        "path" => [],
        "sub_title" => '',
        "summary" => '',
        "content" => '',
        "content_type" => "html",
        "toc" => [],
        "status" => 30,
        "lang" => "",
        "created_at" => "",
        "updated_at" => "",
    ];

    protected $selectCol = [
        'uid',
        'book_id',
        'paragraph',
        'word_start',
        "word_end",
        'channel_uid',
        'content',
        'content_type',
        'editor_uid',
        'acceptor_uid',
        'pr_edit_at',
        'fork_at',
        'create_time',
        'modify_time',
        'created_at',
        'updated_at',
    ];
    protected $wbwChannels = [];
    protected $debug = [];
    protected $userUuid = null;

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
     * Store a newly created resource in storage.

     * @param  \Illuminate\Http\Request  $request
     * @param string $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, string $id)
    {
        $paliService = app(PaliContentService::class);
        if ($request->has('debug')) {
            $this->debug = explode(',', $request->get('debug'));
        }
        $user = AuthApi::current($request);
        if ($user) {
            $this->userUuid = $user['user_uid'];
        }
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
        $paraFrom = $sentId[1];
        $paraTo = $sentId[1] + $chapter->chapter_len - 1;

        if (empty($chapter->toc)) {
            $this->result['title'] = "unknown";
        } else {
            $this->result['title'] = $chapter->toc;
            $this->result['sub_title'] = $chapter->toc;
            $this->result['path'] = json_decode($chapter->path);
        }

        //获取标题
        $heading = PaliText::select(["book", "paragraph", "level"])
            ->where('book', $sentId[0])
            ->whereBetween('paragraph', [$paraFrom, $paraTo])
            ->where('level', '<', 8)
            ->get();
        //将标题段落转成索引数组 以便输出标题层级
        $indexedHeading = [];
        foreach ($heading as $key => $value) {
            # code...
            $indexedHeading["{$value->book}-{$value->paragraph}"] = $value->level;
        }
        #获取channel索引表
        $tranChannels = [];
        $channelInfo = Channel::whereIn("uid", $channels)
            ->select(['uid', 'type', 'lang', 'name'])->get();
        foreach ($channelInfo as $key => $value) {
            # code...
            if ($value->type === "translation") {
                $tranChannels[] = $value->uid;
            }
        }
        $indexChannel = [];
        $indexChannel = $paliService->getChannelIndex($channels);
        //获取wbw channel
        //目前默认的 wbw channel 是第一个translation channel
        //TODO 处理不存在的channel id
        foreach ($channels as $key => $value) {
            # code...
            if (
                isset($indexChannel[$value]) &&
                $indexChannel[$value]->type === 'translation'
            ) {
                $this->wbwChannels[] = $value;
                break;
            }
        }
        $title = Sentence::select($this->selectCol)
            ->where('book_id', $sentId[0])
            ->where('paragraph', $sentId[1])
            ->whereIn('channel_uid', $tranChannels)
            ->first();
        if ($title) {
            $this->result['title'] = MdRender::render($title->content, [$title->channel_uid]);
            $mdRender = new MdRender(['format' => 'simple']);
            $this->result['title_text'] = $mdRender->convert($title->content, [$title->channel_uid]);
        }

        /**
         * 获取句子数据
         * 算法：
         * 1. 如果标题和下一级第一个标题之间有段落。只输出这些段落和子目录
         * 2. 如果标题和下一级第一个标题之间没有间隔 且 chapter 长度大于10000个字符 且有子目录，只输出子目录
         * 3. 如果二者都不是，lazy load
         */
        //1. 计算 标题和下一级第一个标题之间 是否有间隔
        $nextChapter =  PaliText::where('book', $sentId[0])
            ->where('paragraph', ">", $sentId[1])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->value('paragraph');
        $between = $nextChapter - $sentId[1];
        //查找子目录
        $chapterLen = $chapter->chapter_len;
        $toc = PaliText::where('book', $sentId[0])
            ->whereBetween('paragraph', [$paraFrom + 1, $paraFrom + $chapterLen - 1])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->select(['book', 'paragraph', 'level', 'toc'])
            ->get();
        $maxLen = 3000;
        if ($between > 1) {
            //有间隔
            $paraTo = $nextChapter - 1;
        } else {
            if ($chapter->chapter_strlen > $maxLen) {
                if (count($toc) > 0) {
                    //有子目录只输出标题和目录
                    $paraTo = $paraFrom;
                } else {
                    //没有子目录 全部输出
                }
            } else {
                //章节小。全部输出 不输出子目录
                $toc = [];
            }
        }

        $pFrom = $request->get('from', $paraFrom);
        $pTo = $request->get('to', $paraTo);
        //根据句子的长度找到这次应该加载的段落

        $paliText = PaliText::select(['paragraph', 'lenght'])
            ->where('book', $sentId[0])
            ->whereBetween('paragraph', [$pFrom, $pTo])
            ->orderBy('paragraph')
            ->get();
        $sumLen = 0;
        $currTo = $pTo;
        foreach ($paliText as $para) {
            $sumLen += $para->lenght;
            if ($sumLen > $maxLen) {
                $currTo = $para->paragraph;
                break;
            }
        }
        $record = Sentence::select($this->selectCol)
            ->where('book_id', $sentId[0])
            ->whereBetween('paragraph', [$pFrom, $currTo])
            ->whereIn('channel_uid', $channels)
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();
        if (count($record) === 0) {
            return $this->error("no data");
        }
        $this->result['content'] = json_encode($paliService->makeContentObj($record, $mode, $indexChannel), JSON_UNESCAPED_UNICODE);
        $this->result['content_type'] = 'json';
        if (!$request->has('from')) {
            //第一次才显示toc
            $this->result['toc'] = TocResource::collection($toc);
        }
        if ($currTo < $pTo) {
            $this->result['from'] = $currTo + 1;
            $this->result['to'] = $pTo;
            $this->result['paraId'] = $id;
            $this->result['channels'] = $request->get('channels');
            $this->result['mode'] = $request->get('mode');
        }

        return $this->ok($this->result);
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
