<?php

namespace App\Services;

use App\Models\Sentence;
use App\Models\Channel;
use App\Models\PaliText;
use App\Models\ProgressChapter;

use Illuminate\Support\Str;
use App\Http\Api\MdRender;
use App\Http\Api\ChannelApi;



use App\Http\Resources\TocResource;
use App\Services\PaliContentService;
use Illuminate\Support\Facades\Log;



class ChapterService
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

    private $MaxStrLen = 3000;
    public function setMaxSize(int $size)
    {
        $this->MaxStrLen = $size;
    }
    public function chapterWithContent(string $id)
    {
        //getChannels

        //chapter info

        //chapter rang

        //chapter content
    }
    public function paraWithContent(string $id) {}

    public function csParaWithContent(string $id) {}

    public function paraContent(int $book, int $from, int $to) {}
    private function currChapter() {}

    private function getChannels(array|null $input, string $mode)
    {
        $channels = [];
        if (is_array($input)) {
            foreach ($input as  $channel) {
                if (Str::isUuid($channel)) {
                    $channels[] = $channel;
                }
            }
        }

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
        $paliService = app(PaliContentService::class);
        $indexChannel = $paliService->getChannelIndex($channels);
        return [
            'channels' => $channels,
            'index' => $indexChannel,
            'translations' => $tranChannels,
            'request' => $input,
        ];
    }
    private function chapterInfo(int $book, int $para, array $channelInfo)
    {
        $result = [];
        $chapter = PaliText::where('book', $book)->where('paragraph', $para)->first();
        if (!$chapter) {
            //FIXME throw
        }

        if (empty($chapter->toc)) {
            $this->result['title'] = "unknown";
        } else {
            $result['title'] = $chapter->toc;
            $result['sub_title'] = $chapter->toc;
            $result['path'] = json_decode($chapter->path);
        }


        $title = Sentence::select($this->selectCol)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->whereIn('channel_uid', $channelInfo['translation'])
            ->first();
        if ($title) {
            $result['title'] = MdRender::render($title->content, [$title->channel_uid]);
            $mdRender = new MdRender(['format' => 'simple']);
            $result['title_text'] = $mdRender->convert($title->content, [$title->channel_uid]);
        }

        return $result;
    }

    private function chapterContentRang($book, $para, $from, $to)
    {
        /**
         * 获取句子数据
         * 算法：
         * 1. 如果标题和下一级第一个标题之间有段落。只输出这些段落和子目录
         * 2. 如果标题和下一级第一个标题之间没有间隔 且 chapter 长度大于10000个字符 且有子目录，只输出子目录
         * 3. 如果二者都不是，lazy load
         */
        //1. 计算 标题和下一级第一个标题之间 是否有间隔
        $chapter = PaliText::where('book', $book)->where('paragraph', $para)->first();

        $paraFrom = $para;
        $paraTo = $para + $chapter->chapter_len - 1;
        $nextChapter =  PaliText::where('book', $book)
            ->where('paragraph', ">", $para)
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->value('paragraph');
        $between = $nextChapter - $para;

        //查找子目录
        $chapterLen = $chapter->chapter_len;
        $toc = PaliText::where('book', $book)
            ->whereBetween('paragraph', [$paraFrom + 1, $paraFrom + $chapterLen - 1])
            ->where('level', '<', 8)
            ->orderBy('paragraph')
            ->select(['book', 'paragraph', 'level', 'toc'])
            ->get();

        if ($between > 1) {
            //有间隔
            $paraTo = $nextChapter - 1;
        } else {
            if ($chapter->chapter_strlen > $this->MaxStrLen) {
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

        $pFrom = $from ?? $paraFrom;
        $pTo = $to ?? $paraTo;
        //根据句子的长度找到这次应该加载的段落

        $paliText = PaliText::select(['paragraph', 'lenght'])
            ->where('book', $book)
            ->whereBetween('paragraph', [$pFrom, $pTo])
            ->orderBy('paragraph')
            ->get();
        $sumLen = 0;
        $currTo = $pTo;
        foreach ($paliText as $para) {
            $sumLen += $para->lenght;
            if ($sumLen > $this->MaxStrLen) {
                $currTo = $para->paragraph;
                break;
            }
        }
        return ['toc' => $toc, 'from' => $pFrom, 'to' => $currTo];
    }

    private function subChapterToc($toc)
    {
        //第一次才显示toc
        return TocResource::collection($toc);
    }

    private function chapterContent($book, $para, $rang, $channelInfo, $mode)
    {
        $result = [];
        $paliService = app(PaliContentService::class);
        $record = Sentence::select($this->selectCol)
            ->where('book_id', $book)
            ->whereBetween('paragraph', [$rang['from'], $rang['to']])
            ->whereIn('channel_uid', $channelInfo['channels'])
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();
        if (count($record) === 0) {
            Log::warning('no data');
        }
        $result['content'] = json_encode($paliService->makeContentObj(
            $record,
            $mode,
            $channelInfo['index']
        ), JSON_UNESCAPED_UNICODE);
        $result['content_type'] = 'json';

        if ($rang['to'] < $para) {
            $result['from'] = $rang['to'] + 1;
            $result['to'] = $para;
            $result['paraId'] = "{$book}-{$para}";
            $result['channels'] = implode(',', $channelInfo['request']);
            $result['mode'] =  $mode;
        }
        return $result;
    }

    public function publicChannels(int $book, int $para, ?string $type = null)
    {
        $channelIds = ProgressChapter::with('channel')
            ->where('book', $book)
            ->where('para', $para)
            ->whereHas('channel', function ($query) {
                $query->where('status', 30);
            })
            ->select('channel_id')
            ->get();
        $channels = [];
        foreach ($channelIds as  $channel) {
            $channels[] = ChannelApi::getById($channel->channel_id);
        }
        return $channels;
    }

    public function getUidByChannel(int $book, int $para, string $channelId)
    {
        $uid = ProgressChapter::where('book', $book)
            ->where('para', $para)
            ->where('channel_id', $channelId)
            ->value('uid');
        return $uid;
    }
}
