<?php

// api-v8/app/Services/PaliContentService.php

namespace App\Services;

use App\Http\Api\ChannelApi;
use App\Http\Api\MdRender;
use App\Http\Api\StudioApi;
use App\Http\Api\SuggestionApi;
use App\Http\Api\UserApi;
use App\Models\Channel;
use App\Models\Discussion;
use App\Models\PaliSentence;
use App\Models\PaliText;
use App\Models\Sentence;
use App\Models\SentSimIndex;
use App\Models\Wbw;
use App\Models\WbwBlock;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaliContentService
{
    protected $result = [
        'uid' => '',
        'title' => '',
        'path' => [],
        'sub_title' => '',
        'summary' => '',
        'content' => '',
        'content_type' => 'html',
        'toc' => [],
        'status' => 30,
        'lang' => '',
        'created_at' => '',
        'updated_at' => '',
    ];

    protected $wbwChannels = [];

    // 句子需要查询的列
    protected $selectCol = [
        'uid',
        'book_id',
        'paragraph',
        'word_start',
        'word_end',
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

    protected $userUuid = null;

    protected $debug = [];

    public static function _sentCanReadCount($book, $para, $start, $end, $userUuid = null)
    {
        $keyCanRead = '/channel/can-read/';
        if ($userUuid) {
            $keyCanRead .= $userUuid;
        } else {
            $keyCanRead .= 'guest';
        }
        $channelCanRead = Cache::remember(
            $keyCanRead,
            config('mint.cache.expire'),
            function () use ($userUuid) {
                return ChannelApi::getCanReadByUser($userUuid);
            }
        );
        $channels = Sentence::where('book_id', $book)
            ->where('paragraph', $para)
            ->where('word_start', $start)
            ->where('word_end', $end)
            ->where('strlen', '<>', 0)
            ->whereIn('channel_uid', $channelCanRead)
            ->select('channel_uid')
            ->groupBy('channel_uid')
            ->get();
        $channelList = [];
        foreach ($channels as $key => $value) {
            // code...
            if (Str::isUuid($value->channel_uid)) {
                $channelList[] = $value->channel_uid;
            }
        }
        $simId = PaliSentence::where('book', $book)
            ->where('paragraph', $para)
            ->where('word_begin', $start)
            ->where('word_end', $end)
            ->value('id');
        if ($simId) {
            $output['simNum'] = SentSimIndex::where('sent_id', $simId)->value('count');
        } else {
            $output['simNum'] = 0;
        }
        $channelInfo = Channel::whereIn('uid', $channelList)->select('type')->get();
        $output['tranNum'] = 0;
        $output['nissayaNum'] = 0;
        $output['commNum'] = 0;
        $output['originNum'] = 0;

        foreach ($channelInfo as $key => $value) {
            // code...
            switch ($value->type) {
                case 'translation':
                    $output['tranNum']++;
                    break;
                case 'nissaya':
                    $output['nissayaNum']++;
                    break;
                case 'commentary':
                    $output['commNum']++;
                    break;
                case 'original':
                    $output['originNum']++;
                    break;
            }
        }

        return $output;
    }

    private function newSent($book, $para, $word_start, $word_end)
    {
        $sent = [
            'id' => "{$book}-{$para}-{$word_start}-{$word_end}",
            'book' => $book,
            'para' => $para,
            'wordStart' => $word_start,
            'wordEnd' => $word_end,
            'origin' => [],
            'translation' => [],
            'commentaries' => [],
        ];

        if ($book < 1000) {
            // 生成channel 数量列表
            $sentId = "{$book}-{$para}-{$word_start}-{$word_end}";
            $channelCount = self::_sentCanReadCount($book, $para, $word_start, $word_end, $this->userUuid);
            $path = json_decode(PaliText::where('book', $book)->where('paragraph', $para)->value('path'), true);
            $sent['path'] = [];
            foreach ($path as $key => $value) {
                // code...
                $value['paliTitle'] = $value['title'];
                $sent['path'][] = $value;
            }
            $sent['tranNum'] = $channelCount['tranNum'];
            $sent['nissayaNum'] = $channelCount['nissayaNum'];
            $sent['commNum'] = $channelCount['commNum'];
            $sent['originNum'] = $channelCount['originNum'];
            $sent['simNum'] = $channelCount['simNum'];
        }

        return $sent;
    }

    /**
     * 根据句子库数据生成以段落为单位的文章内容
     * $record 句子数据
     * $mode read | edit | wbw
     * $indexChannel channel索引
     * $indexedHeading 标题索引 用于给段落加标题标签 <h1> ect.
     */
    public function makeContentObj($record, $mode, $indexChannel, $format = 'react')
    {
        $content = [];

        // 获取句子编号列表
        $paraIndex = [];
        foreach ($record as $value) {
            $currSentId = "{$value->book_id}-{$value->paragraph}-{$value->word_start}-{$value->word_end}";
            $value->sid = "{$currSentId}_{$value->channel_uid}";

            $currParaId = "{$value->book_id}-{$value->paragraph}";
            if (! isset($paraIndex[$currParaId])) {
                $paraIndex[$currParaId] = [];
            }
            $paraIndex[$currParaId][] = $value;
        }
        $channelsId = [];
        foreach ($indexChannel as $channelId => $info) {
            $channelsId[] = $channelId;
        }
        array_pop($channelsId);
        // 遍历列表查找每个句子的所有channel的数据，并填充
        $paragraphs = [];
        foreach ($paraIndex as $currParaId => $sentData) {
            $arrParaId = explode('-', $currParaId);
            $sentIndex = [];
            foreach ($sentData as $sent) {
                $currSentId = "{$sent->book_id}-{$sent->paragraph}-{$sent->word_start}-{$sent->word_end}";
                $sentIndex[$currSentId] = [$sent->book_id, $sent->paragraph, $sent->word_start, $sent->word_end];
            }
            $sentInPara = array_values($sentIndex);
            $paraProps = [
                'book' => $arrParaId[0],
                'para' => $arrParaId[1],
                'channels' => $channelsId,
                'sentences' => $sentInPara,
                'mode' => $mode,
                'children' => [],
            ];
            // 建立段落里面的句子列表
            foreach ($sentIndex as $ids => $arrSentId) {
                $sentNode = $this->newSent($arrSentId[0], $arrSentId[1], $arrSentId[2], $arrSentId[3]);
                foreach ($indexChannel as $channelId => $info) {
                    // code...
                    $sid = "{$ids}_{$channelId}";
                    if (isset($info->studio)) {
                        $studioInfo = $info->studio;
                    } else {
                        $studioInfo = null;
                    }
                    $newSent = [
                        'content' => '',
                        'html' => '',
                        'book' => $arrSentId[0],
                        'para' => $arrSentId[1],
                        'wordStart' => $arrSentId[2],
                        'wordEnd' => $arrSentId[3],
                        'channel' => [
                            'name' => $info->name,
                            'type' => $info->type,
                            'id' => $info->uid,
                            'lang' => $info->lang,
                        ],
                        'studio' => $studioInfo,
                        'updateAt' => '',
                        'suggestionCount' => SuggestionApi::getCountBySent($arrSentId[0], $arrSentId[1], $arrSentId[2], $arrSentId[3], $channelId),
                    ];

                    $row = Arr::first($sentData, function ($value, $key) use ($sid) {
                        return $value->sid === $sid;
                    });
                    if ($row) {
                        $newSent['id'] = $row->uid;
                        $newSent['content'] = $row->content;
                        $newSent['contentType'] = $row->content_type;
                        $newSent['html'] = '';
                        $newSent['editor'] = UserApi::getByUuid($row->editor_uid);
                        /**
                         * TODO 刷库改数据
                         * 旧版api没有更新updated_at所以造成旧版的数据updated_at数据比modify_time 要晚
                         */
                        $newSent['forkAt'] = $row->fork_at; //
                        $newSent['updateAt'] = $row->updated_at; //
                        $newSent['updateAt'] = date('Y-m-d H:i:s.', $row->modify_time / 1000).($row->modify_time % 1000).' UTC';

                        $newSent['createdAt'] = $row->created_at;
                        if ($mode !== 'read') {
                            if (isset($row->acceptor_uid) && ! empty($row->acceptor_uid)) {
                                $newSent['acceptor'] = UserApi::getByUuid($row->acceptor_uid);
                                $newSent['prEditAt'] = $row->pr_edit_at;
                            }
                        }
                        switch ($info->type) {
                            case 'wbw':
                            case 'original':
                                //
                                // 在编辑模式下。
                                // 如果是原文，查看是否有逐词解析数据，
                                // 有的话优先显示。
                                // 阅读模式直接显示html原文
                                // 传过来的数据一定有一个原文channel
                                //
                                if ($mode === 'read') {
                                    $newSent['content'] = '';
                                    $newSent['html'] = MdRender::render(
                                        $row->content,
                                        [$row->channel_uid],
                                        null,
                                        $mode,
                                        'translation',
                                        $row->content_type,
                                        $format
                                    );
                                } else {
                                    if ($row->content_type === 'json') {
                                        $newSent['channel']['type'] = 'wbw';
                                        if (isset($this->wbwChannels[0])) {
                                            $newSent['channel']['name'] = $indexChannel[$this->wbwChannels[0]]->name;
                                            $newSent['channel']['lang'] = $indexChannel[$this->wbwChannels[0]]->lang;
                                            $newSent['channel']['id'] = $this->wbwChannels[0];
                                            // 存在一个translation channel
                                            // 尝试查找逐词解析数据。找到，替换现有数据
                                            $wbwData = $this->getWbw(
                                                $arrSentId[0],
                                                $arrSentId[1],
                                                $arrSentId[2],
                                                $arrSentId[3],
                                                $this->wbwChannels[0]
                                            );
                                            if ($wbwData) {
                                                $newSent['content'] = $wbwData;
                                                $newSent['contentType'] = 'json';
                                                $newSent['html'] = '';
                                                $newSent['studio'] = $indexChannel[$this->wbwChannels[0]]->studio;
                                            }
                                        }
                                    } else {
                                        $newSent['content'] = $row->content;
                                        $newSent['html'] = MdRender::render(
                                            $row->content,
                                            [$row->channel_uid],
                                            null,
                                            $mode,
                                            'translation',
                                            $row->content_type,
                                            $format
                                        );
                                    }
                                }

                                break;
                            case 'nissaya':
                                $newSent['html'] = Cache::remember(
                                    "/sent/{$channelId}/{$ids}/{$format}",
                                    config('mint.cache.expire'),
                                    function () use ($row, $mode, $format) {
                                        if ($row->content_type === 'markdown') {
                                            return MdRender::render(
                                                $row->content,
                                                [$row->channel_uid],
                                                null,
                                                $mode,
                                                'nissaya',
                                                $row->content_type,
                                                $format
                                            );
                                        } else {
                                            return null;
                                        }
                                    }
                                );
                                break;
                            case 'commentary':
                                $options = [
                                    'debug' => $this->debug,
                                    'format' => $format,
                                    'mode' => $mode,
                                    'channelType' => 'translation',
                                    'contentType' => $row->content_type,
                                ];
                                $mdRender = new MdRender($options);
                                $newSent['html'] = $mdRender->convert($row->content, $channelsId);
                                break;
                            default:
                                $options = [
                                    'debug' => $this->debug,
                                    'format' => $format,
                                    'mode' => $mode,
                                    'channelType' => 'translation',
                                    'contentType' => $row->content_type,
                                ];
                                $mdRender = new MdRender($options);
                                $newSent['html'] = $mdRender->convert($row->content, [$row->channel_uid]);
                                // Log::debug('md render', ['content' => $row->content, 'options' => $options, 'render' => $newSent['html']]);
                                break;
                        }
                    } else {
                        Log::warning('no sentence record');
                    }
                    switch ($info->type) {
                        case 'wbw':
                        case 'original':
                            array_push($sentNode['origin'], $newSent);
                            break;
                        case 'commentary':
                            array_push($sentNode['commentaries'], $newSent);
                            break;
                        default:
                            array_push($sentNode['translation'], $newSent);
                            break;
                    }
                }
                $paraProps['children'][] = $sentNode;
            }
            $paragraphs[] = $paraProps;
        }

        return $paragraphs;
    }

    public function getWbw($book, $para, $start, $end, $channel)
    {
        /**
         * 非阅读模式下。原文使用逐词解析数据。
         * 优先加载第一个translation channel 如果没有。加载默认逐词解析。
         */

        // 获取逐词解析数据
        $wbwBlock = WbwBlock::where('channel_uid', $channel)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->select('uid')
            ->first();
        if (! $wbwBlock) {
            return false;
        }
        // 找到逐词解析数据
        $wbwData = Wbw::where('block_uid', $wbwBlock->uid)
            ->whereBetween('wid', [$start, $end])
            ->select(['book_id', 'paragraph', 'wid', 'data', 'uid', 'editor_id', 'created_at', 'updated_at'])
            ->orderBy('wid')
            ->get();
        $wbwContent = [];
        foreach ($wbwData as $wbwrow) {
            $wbw = str_replace('&nbsp;', ' ', $wbwrow->data);
            $wbw = str_replace('<br>', ' ', $wbw);

            $xmlString = '<root>'.$wbw.'</root>';
            try {
                $xmlWord = simplexml_load_string($xmlString);
            } catch (\Exception $e) {
                Log::error('corpus', ['error' => $e]);

                continue;
            }
            $wordsList = $xmlWord->xpath('//word');
            foreach ($wordsList as $word) {
                $case = \str_replace(['#', '.'], ['$', ''], $word->case->__toString());
                $case = \str_replace('$$', '$', $case);
                $case = trim($case);
                $case = trim($case, '$');
                $wbwId = explode('-', $word->id->__toString());

                $wbwData = [
                    'uid' => $wbwrow->uid,
                    'book' => $wbwrow->book_id,
                    'para' => $wbwrow->paragraph,
                    'sn' => array_slice($wbwId, 2),
                    'word' => ['value' => $word->pali->__toString(), 'status' => 0],
                    'real' => ['value' => $word->real->__toString(), 'status' => 0],
                    'meaning' => ['value' => $word->mean->__toString(), 'status' => 0],
                    'type' => ['value' => $word->type->__toString(), 'status' => 0],
                    'grammar' => ['value' => $word->gramma->__toString(), 'status' => 0],
                    'case' => ['value' => $word->case->__toString(), 'status' => 0],
                    'parent' => ['value' => $word->parent->__toString(), 'status' => 0],
                    'style' => ['value' => $word->style->__toString(), 'status' => 0],
                    'factors' => ['value' => $word->org->__toString(), 'status' => 0],
                    'factorMeaning' => ['value' => $word->om->__toString(), 'status' => 0],
                    'confidence' => $word->cf->__toString(),
                    'created_at' => $wbwrow->created_at,
                    'updated_at' => $wbwrow->updated_at,
                    'hasComment' => Discussion::where('res_id', $wbwrow->uid)->exists(),
                ];
                if (isset($word->parent2)) {
                    $wbwData['parent2']['value'] = $word->parent2->__toString();
                    if (isset($word->parent2['status'])) {
                        $wbwData['parent2']['status'] = (int) $word->parent2['status'];
                    } else {
                        $wbwData['parent2']['status'] = 0;
                    }
                }
                if (isset($word->pg)) {
                    $wbwData['grammar2']['value'] = $word->pg->__toString();
                    if (isset($word->pg['status'])) {
                        $wbwData['grammar2']['status'] = (int) $word->pg['status'];
                    } else {
                        $wbwData['grammar2']['status'] = 0;
                    }
                }
                if (isset($word->rela)) {
                    $wbwData['relation']['value'] = $word->rela->__toString();
                    if (isset($word->rela['status'])) {
                        $wbwData['relation']['status'] = (int) $word->rela['status'];
                    } else {
                        $wbwData['relation']['status'] = 7;
                    }
                }
                if (isset($word->bmt)) {
                    $wbwData['bookMarkText']['value'] = $word->bmt->__toString();
                    if (isset($word->bmt['status'])) {
                        $wbwData['bookMarkText']['status'] = (int) $word->bmt['status'];
                    } else {
                        $wbwData['bookMarkText']['status'] = 7;
                    }
                }
                if (isset($word->bmc)) {
                    $wbwData['bookMarkColor']['value'] = $word->bmc->__toString();
                    if (isset($word->bmc['status'])) {
                        $wbwData['bookMarkColor']['status'] = (int) $word->bmc['status'];
                    } else {
                        $wbwData['bookMarkColor']['status'] = 7;
                    }
                }
                if (isset($word->note)) {
                    $wbwData['note']['value'] = $word->note->__toString();
                    if (isset($word->note['status'])) {
                        $wbwData['note']['status'] = (int) $word->note['status'];
                    } else {
                        $wbwData['note']['status'] = 7;
                    }
                }
                if (isset($word->cf)) {
                    $wbwData['confidence'] = (float) $word->cf->__toString();
                }
                if (isset($word->attachments)) {
                    $wbwData['attachments'] = json_decode($word->attachments->__toString());
                }
                if (isset($word->pali['status'])) {
                    $wbwData['word']['status'] = (int) $word->pali['status'];
                }
                if (isset($word->real['status'])) {
                    $wbwData['real']['status'] = (int) $word->real['status'];
                }
                if (isset($word->mean['status'])) {
                    $wbwData['meaning']['status'] = (int) $word->mean['status'];
                }
                if (isset($word->type['status'])) {
                    $wbwData['type']['status'] = (int) $word->type['status'];
                }
                if (isset($word->gramma['status'])) {
                    $wbwData['grammar']['status'] = (int) $word->gramma['status'];
                }
                if (isset($word->case['status'])) {
                    $wbwData['case']['status'] = (int) $word->case['status'];
                }
                if (isset($word->parent['status'])) {
                    $wbwData['parent']['status'] = (int) $word->parent['status'];
                }
                if (isset($word->org['status'])) {
                    $wbwData['factors']['status'] = (int) $word->org['status'];
                }
                if (isset($word->om['status'])) {
                    $wbwData['factorMeaning']['status'] = (int) $word->om['status'];
                }

                $wbwContent[] = $wbwData;
            }
        }
        if (count($wbwContent) === 0) {
            return false;
        }

        return \json_encode($wbwContent, JSON_UNESCAPED_UNICODE);
    }

    public function getChannelIndex($channels, $type = null)
    {
        // 获取channel索引表
        $channelInfo = Channel::whereIn('uid', $channels)
            ->select(['uid', 'type', 'name', 'lang', 'owner_uid'])
            ->get();
        $indexChannel = [];
        foreach ($channels as $key => $channelId) {
            $channelInfo = Channel::where('uid', $channelId)
                ->select(['uid', 'type', 'name', 'lang', 'owner_uid'])->first();
            if (! $channelInfo) {
                Log::error('no channel id'.$channelId);

                continue;
            }
            if ($type !== null && $channelInfo->type !== $type) {
                continue;
            }
            $indexChannel[$channelId] = $channelInfo;
            $indexChannel[$channelId]->studio = StudioApi::getById($channelInfo->owner_uid);
        }

        return $indexChannel;
    }

    public function sentences(array $sentenceIds, array $channelIds, string $mode)
    {
        $query = [];
        foreach ($sentenceIds as $id) {
            // code...
            $query[] = explode('-', $id);
        }
        $record = Sentence::select($this->selectCol)
            ->whereIns(['book_id', 'paragraph', 'word_start', 'word_end'], $query)
            ->whereIn('channel_uid', $channelIds)
            ->get();
        $indexChannel = $this->getChannelIndex($channelIds);
        $result = $this->makeContentObj($record, $mode, $indexChannel);

        return $result;
    }

    public function paragraphs(int $book, int $start, int $end, array $channelIds, array $param)
    {

        $channels = [];
        foreach ($channelIds as $key => $channel) {
            if (Str::isUuid($channel)) {
                $channels[] = $channel;
            }
        }
        $channelId = false;
        if ($param['original']) {
            if ($param['mode'] === 'read') {
                // 阅读模式加载html格式原文
                $channelId = ChannelApi::getSysChannel('_System_Pali_VRI_');
            } else {
                // 翻译模式加载json格式原文
                $channelId = ChannelApi::getSysChannel('_System_Wbw_VRI_');
            }
        }

        if ($channelId !== false) {
            $channels[] = $channelId;
        }
        // 获取channel索引表
        $tranChannels = [];
        $channelInfo = Channel::whereIn('uid', $channels)
            ->select(['uid', 'type', 'lang', 'name'])->get();
        foreach ($channelInfo as $key => $value) {
            // code...
            if ($value->type === 'translation') {
                $tranChannels[] = $value->uid;
            }
        }
        $indexChannel = [];
        $paliService = app(PaliContentService::class);
        $indexChannel = $paliService->getChannelIndex($channels);
        // the end of channel

        // content
        $record = Sentence::select($this->selectCol)
            ->where('book_id', $book)
            ->whereBetween('paragraph', [$start, $end])
            ->whereIn('channel_uid', $channels)
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get();

        $result = $paliService->makeContentObj($record, $param['mode'], $indexChannel, $param['format']);

        return $result;
    }

    /**
     * 阅读模式渲染单个段落。
     * 直接从 sentences 表取单个 channel 的记录，渲染成 html。
     * 与 paragraphs() 不同：没有译文的句子不保留占位，有多少句子输出多少句子。
     *
     * @param  int  $level  标题级别，大于 0 时段落用 h{level} 包裹
     * @return array{para: int, display: string, sentences: array<int, array{sid: string, html: string}>}
     */
    public function readParagraph(int $book, int $para, string $channelUid, int $level = 0, string $format = 'html'): array
    {
        $version = Cache::get(self::paragraphVersionKey($book, $para, $channelUid), 0);
        $key = "/read-para/{$book}-{$para}/{$channelUid}/{$level}/{$format}/{$version}";

        return Cache::rememberForever($key, function () use ($book, $para, $channelUid, $level, $format) {
            return $this->renderReadParagraph($book, $para, $channelUid, $level, $format);
        });
    }

    /**
     * 段落缓存版本号的 key。句子有增改删时版本号加一，相关缓存自然失效。
     */
    public static function paragraphVersionKey(int $book, int $para, string $channelUid): string
    {
        return "/read-para/version/{$book}-{$para}/{$channelUid}";
    }

    /**
     * 使某个段落的阅读模式缓存失效
     */
    public static function forgetParagraph(int $book, int $para, string $channelUid): void
    {
        $key = self::paragraphVersionKey($book, $para, $channelUid);
        if (Cache::has($key)) {
            Cache::increment($key);
        } else {
            Cache::forever($key, 1);
        }
    }

    /**
     * @return array{para: int, display: string, sentences: array<int, array{sid: string, html: string}>}
     */
    protected function renderReadParagraph(int $book, int $para, string $channelUid, int $level, string $format): array
    {
        $result = ['para' => $para, 'display' => '', 'sentences' => []];
        $channel = Channel::where('uid', $channelUid)
            ->select(['uid', 'type', 'lang', 'name'])->first();
        if (! $channel) {
            return $result;
        }
        $isOrigin = $channel->type === 'original' || $channel->type === 'wbw';
        $channelType = $channel->type === 'nissaya' ? 'nissaya' : 'translation';

        $records = Sentence::select($this->selectCol)
            ->where('book_id', $book)
            ->where('paragraph', $para)
            ->where('channel_uid', $channelUid)
            ->orderBy('word_start')
            ->get();

        $sentences = [];
        foreach ($records as $row) {
            $html = MdRender::render(
                $row->content,
                [$row->channel_uid],
                null,
                'read',
                $channelType,
                $row->content_type,
                $format
            );
            if (empty($html)) {
                continue;
            }
            $sid = "{$row->book_id}-{$row->paragraph}-{$row->word_start}-{$row->word_end}";
            $result['sentences'][] = ['sid' => $sid, 'html' => $html];
            if ($format === 'html') {
                $class = $isOrigin ? 'sentence origin' : 'sentence';
                $sentences[] = "<div class='{$class}' data-sid='{$sid}'>{$html}</div>";
            } else {
                $sentences[] = $html;
            }
        }

        if (count($sentences) === 0) {
            return $result;
        }

        if ($format === 'html') {
            // html 格式加段落外壳
            $area = $isOrigin ? 'original' : 'translation';
            $content = implode('', $sentences);
            $inner = $level > 0 ? "<h{$level}>{$content}</h{$level}>" : "<div class='para-block'>{$content}</div>";
            $result['display'] = "<div class='{$area}' data-para='{$para}'>{$inner}</div>";
        } else {
            // 其他格式一行一句
            $result['display'] = implode("\n", $sentences);
        }

        return $result;
    }
}
