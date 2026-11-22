<?php

// api-v8/app/Services/PaliContentService.php

namespace App\Services;

use App\Facades\RedisCache;
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
use App\Services\Markup\Annotation\AnnotationSet;
use App\Services\Markup\Format;
use App\Services\Markup\MarkupService;
use App\Services\Markup\RenderOptions;
use App\Services\V3\PaliTextService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
        $channelCanRead = RedisCache::remember(
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

        // 逐词解析（wbw）默认使用第一个 translation channel 的数据。
        // 系统原文 channel 固定在末尾（见 CorpusController / ChapterContentController 的约定），
        // 这里从 indexChannel 挑出 translation channel 作为 wbw 的读写目标；
        // 没有 translation channel 时（如直接编辑系统原文）保持为空，回落到系统原文。
        $this->wbwChannels = [];
        foreach ($indexChannel as $channelId => $info) {
            if ($info->type === 'translation') {
                $this->wbwChannels[] = $channelId;
                break;
            }
        }

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
                                $newSent['html'] = RedisCache::remember(
                                    "/sent/{$channelId}/{$ids}/{$format}",
                                    config('mint.cache.expire'),
                                    function () use ($row, $mode, $format) {
                                        return MdRender::render(
                                            $row->content,
                                            [$row->channel_uid],
                                            null,
                                            $mode,
                                            'nissaya',
                                            $row->content_type,
                                            $format
                                        );
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
                $channelId = app(ChannelService::class)->systemUid('_System_Pali_VRI_');
            } else {
                // 翻译模式加载json格式原文
                $channelId = app(ChannelService::class)->systemUid('_System_Wbw_VRI_');
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
     * 阅读模式渲染单个段落，即只给一段的 {@see self::readParagraphs()}。
     * 直接从 sentences 表取单个 channel 的记录。与 paragraphs() 不同：没有译文的句子不保留占位，
     * 有多少句子输出多少句子。
     *
     * @return array{para: int, display: string, sentences: list<array{sid: string, html: string}>}
     */
    public function readParagraph(int $book, int $para, string $channelUid, string $format = 'html'): array
    {
        return $this->readParagraphs($book, [$para], $channelUid, $format)[$para];
    }

    /**
     * 阅读模式渲染同一本书的多个段落。v3 阅读、Laravel 阅读器、三藏下载都从这里取正文。
     *
     * 每段的**完整结果**（含段落外壳与段后脚注列表）按 (book, para, channel, format) 缓存，
     * 命中时不查库；没有译文的段也缓存（display 为空）。没命中的段一起渲染
     * （{@see self::renderReadParagraphs()}）：channel、句子、批注各查一次，全部句子一次交给 Markup，
     * 术语、引用句子等每类只查一次。
     *
     * 缓存由 Sentence / Discussion 的模型事件删除（{@see self::paragraphChanged()}、{@see self::forgetParagraph()}）；术语释义、
     * 引用的句子、channel 名称改了没有失效信号，最多旧 `mint.cache.read_paragraph_ttl`。
     *
     * @param  list<int>  $paras
     * @return array<int, array{para: int, display: string, sentences: list<array{sid: string, html: string}>}> 段落号 => 结果，顺序同 $paras
     */
    public function readParagraphs(int $book, array $paras, string $channelUid, string $format = 'html'): array
    {
        $results = [];
        $misses = [];
        foreach (array_unique($paras) as $para) {
            $cached = RedisCache::get(self::paragraphCacheKey($book, $para, $channelUid, $format));
            $results[$para] = is_array($cached) ? $cached : null;
            if ($results[$para] === null) {
                $misses[] = $para;
            }
        }
        if ($misses === []) {
            return $results;
        }

        $ttl = (int) config('mint.cache.read_paragraph_ttl');
        foreach ($this->renderReadParagraphs($book, $misses, $channelUid, $format) as $para => $result) {
            RedisCache::put(self::paragraphCacheKey($book, $para, $channelUid, $format), $result, $ttl);
            $results[$para] = $result;
        }

        return $results;
    }

    /**
     * 区间 [start, end] 的阅读模式正文（html），阅读器按章读用。
     *
     * 按平铺章节缓存（{@see PaliTextService::flatChapters()}：标题段到下一个标题段之前是一章，不嵌套），
     * 一章一个 key，命中时整章一次 GET、不查库。区间完整覆盖的章走章节缓存，没命中的章一起交给
     * {@see self::readParagraphs()}（各段多半已在段落成品缓存里）拼好回写；区间边缘没盖全的章、
     * 第一个标题段之前的段直接逐段取。章节缓存与段落成品一起失效（{@see self::forgetParagraph()}）。
     */
    public function readChapters(int $book, int $start, int $end, string $channelUid, string $format = 'html'): string
    {
        // 区间切成片：[起, 止, 是否整章]
        $pieces = [];
        $cursor = $start;
        foreach (app(PaliTextService::class)->flatChapters($book) as $chapterStart => $chapterEnd) {
            if ($chapterEnd < $cursor || $chapterStart > $end) {
                continue;
            }
            if ($chapterStart > $cursor) {
                $pieces[] = [$cursor, $chapterStart - 1, false];
                $cursor = $chapterStart;
            }
            $whole = $chapterStart >= $start && $chapterEnd <= $end;
            $pieces[] = [$cursor, min($chapterEnd, $end), $whole];
            $cursor = min($chapterEnd, $end) + 1;
        }
        if ($cursor <= $end) {
            $pieces[] = [$cursor, $end, false];
        }

        $chapters = [];
        $paras = [];
        foreach ($pieces as $i => [$from, $to, $whole]) {
            if ($whole) {
                $cached = RedisCache::get(self::chapterCacheKey($book, $from, $channelUid, $format));
                if (is_string($cached)) {
                    $chapters[$i] = $cached;

                    continue;
                }
            }
            array_push($paras, ...range($from, $to));
        }
        $rendered = $paras === [] ? [] : $this->readParagraphs($book, $paras, $channelUid, $format);

        $ttl = (int) config('mint.cache.read_paragraph_ttl');
        $separator = $format === 'html' ? '' : "\n";
        $display = [];
        foreach ($pieces as $i => [$from, $to, $whole]) {
            if (! isset($chapters[$i])) {
                $parts = [];
                foreach (range($from, $to) as $para) {
                    if (($rendered[$para]['display'] ?? '') !== '') {
                        $parts[] = $rendered[$para]['display'];
                    }
                }
                $chapters[$i] = implode($separator, $parts);
                if ($whole) {
                    RedisCache::put(self::chapterCacheKey($book, $from, $channelUid, $format), $chapters[$i], $ttl);
                }
            }
            if ($chapters[$i] !== '') {
                $display[] = $chapters[$i];
            }
        }

        return implode($separator, $display);
    }

    /**
     * 段落是章节标题时返回标题级别，否则 0
     */
    public function paragraphLevel(int $book, int $para): int
    {
        return app(PaliTextService::class)->headingLevel($book, $para);
    }

    /**
     * 阅读模式支持的全部格式。forgetParagraph 需要逐格式清除缓存 key。
     *
     * @var array<int, string>
     */
    private const FORMATS = ['html', 'markdown', 'text'];

    /**
     * 阅读格式 → App\Services\Markup 的输出格式。
     */
    private static function markupFormat(string $format): Format
    {
        return match ($format) {
            'markdown' => Format::Markdown,
            'text' => Format::Text,
            default => Format::Html,
        };
    }

    /**
     * 义注对应（commentary）批注最多渲染几句。
     * 一条批注可能挂着十几句义注，全铺进边注会把正文挤没，只取前几句。
     */
    private const MAX_COMMENTARY_SENTENCES = 3;

    /**
     * 段落阅读模式缓存的 key（段落成品：含外壳与脚注列表）。
     */
    public static function paragraphCacheKey(int $book, int $para, string $channelUid, string $format): string
    {
        // v4：2026-11-22 起缓存整段成品（原来只缓存句子、外壳每次现查），换前缀让旧缓存自然过期
        return "/read-para/v4/{$book}-{$para}/{$channelUid}/{$format}";
    }

    /**
     * 平铺章节的阅读正文缓存 key（{@see self::readChapters()}）。
     */
    public static function chapterCacheKey(int $book, int $chapter, string $channelUid, string $format): string
    {
        return "/read-chapter/v1/{$book}-{$chapter}/{$channelUid}/{$format}";
    }

    /**
     * 删除某个段落的阅读模式缓存（段落成品，及它所在平铺章节的章节缓存），下次读时自动重建。
     */
    public static function forgetParagraph(int $book, int $para, string $channelUid): void
    {
        $chapter = app(PaliTextService::class)->flatChapterOf($book, $para);
        foreach (self::FORMATS as $format) {
            RedisCache::forget(self::paragraphCacheKey($book, $para, $channelUid, $format));
            if ($chapter !== null) {
                RedisCache::forget(self::chapterCacheKey($book, $chapter, $channelUid, $format));
            }
        }
    }

    /**
     * 某段的句子变了（Sentence 的模型事件调用）：删段落成品与所在章节的缓存；「有译文的段」可能变了
     * （$indexChanged：新建 / 删除句子、strlen 在 0 与非 0 之间变化、句子搬了家）时再删内容索引。
     * 书号索引的两个参数见 {@see ReadingIndexService::forget()}。
     */
    public static function paragraphChanged(int $book, int $para, string $channelUid, bool $indexChanged = false, bool $mayAddBook = false, bool $mayRemoveBook = false): void
    {
        self::forgetParagraph($book, $para, $channelUid);
        if ($indexChanged) {
            app(ReadingIndexService::class)->forget($channelUid, $book, $mayAddBook, $mayRemoveBook);
        }
    }

    /**
     * 段落批注内嵌：把锚定到该句子的批注排进句子 content，再交给 App\Services\Markup 渲染。
     *
     * 四种批注，显示方式不同（排版规则与交叠处理见 {@see AnnotationLayout}）：
     * - `commentary`（注释书对应）：content 是义注 / 复注的句子模板 `{{book-para-start-end}}`，
     *   渲染成这几句的译文，作为 {{note}} 边注插在锚点末尾，并追加 <cite> 跳转出处；
     * - `note`（普通边注）：content 就是注解正文（markdown），同样插成 {{note}}，没有出处；
     * - `glossary`（外挂术语）：锚点处的原文换成 `[[title#原文]]`，渲染成术语（title 是词头）；
     * - `issue`：锚点处的原文包一层高亮，带 discussion id，阅读器点击看内容；
     *   没有位置的合成一个三角叹号图标放在句尾。纯文本 / markdown 里不显示。
     * - `assessment`（译文评估）：句尾一个对勾图标，颜色按分数分档，带 discussion id，
     *   阅读器点击看分数与理由。一句只有一条（有并发残留时取最近更新的）。纯文本 / markdown 里不显示。
     *   同段里其它没有评估的句子由 {@see self::renderReadParagraphs()} 补灰色对勾。
     * issue / glossary 只注入未关闭的；批注与评估没有开关状态。
     *
     * 位置按 pos_* 定位，与 quote_exact 对不上（正文改过）时按摘录重新定位。
     *
     * 插入点写的是结构化标记（{@see AnnotationSet}）而不是 HTML：content 里留下占位符，渲染时变成
     * anno-issue / anno-issue-mark / anno-assessment / note 模板节点，任意格式都能输出。
     *
     * 批注由调用方一次查好传进来（{@see self::readingAnnotations()}）。段后脚注列表的正文（纯文本）
     * 这里不渲染，只给出待渲染的源码（`text`），由调用方整块一次渲染后填进 `noteHtml`。
     *
     * @param  list<Discussion>  $records  挂在这句上、要注入阅读页的批注，按创建时间升序
     * @return array{content: string, notes: list<array{fnId: string, noteHtml: string, citeHtml: string, text: array{source: string, channels: list<string>, contentType: string}, truncated: bool}>, assessed: bool, annotations: AnnotationSet}
     */
    protected function injectAnnotationNotes(Sentence $row, string $channelType, array $records): array
    {
        $content = (string) $row->content;
        $annotations = new AnnotationSet;
        $result = ['content' => $content, 'notes' => [], 'assessed' => false, 'annotations' => $annotations];

        if ($records === []) {
            return $result;
        }

        $len = mb_strlen($content, 'UTF-8');
        $locate = fn (Discussion $d) => AnnotationLayout::locate(
            $content, $d->pos_start, $d->pos_end, $d->quote_exact, $d->quote_prefix, $d->quote_suffix
        );
        $glossaries = [];
        $issues = [];
        $notes = [];
        $footnotes = [];
        $assessment = null;
        foreach ($records as $record) {
            if ($record->type === Discussion::TYPE_ASSESSMENT) {
                if ($record->score !== null && ($assessment === null || $record->updated_at >= $assessment['updated_at'])) {
                    $assessment = ['id' => $record->id, 'score' => (int) $record->score, 'updated_at' => $record->updated_at];
                }

                continue;
            }
            if ($record->type === Discussion::TYPE_GLOSSARY) {
                $range = $locate($record);
                if ($range !== null) {
                    $glossaries[] = ['id' => $record->id, 'start' => $range[0], 'end' => $range[1], 'title' => (string) $record->title];
                }

                continue;
            }
            if ($record->type === 'issue') {
                $range = $locate($record);
                $issues[] = ['id' => $record->id, 'start' => $range[0] ?? null, 'end' => $range[1] ?? null, 'severity' => $record->severity];

                continue;
            }
            $note = $this->renderAnnotationNote($record, $row);
            if ($note === null) {
                continue;
            }
            // 插入点：锚点末尾；定位不到时沿用 pos_end，null / 越界都算句尾
            $range = $locate($record);
            $at = $range[1] ?? $record->pos_end;
            if ($at === null || $at < 0 || $at > $len) {
                $at = $len;
            }
            $notes[] = [
                'id' => $record->id,
                'at' => $at,
                'order' => $note['order'],
                'html' => $annotations->note($note['body'], $note['params'], $record->effectiveContentType()),
            ];
            $footnotes[$record->id] = [
                'fnId' => 'fn-'.$record->id,
                'noteHtml' => '',
                'citeHtml' => $note['citeHtml'],
                'text' => $note['text'],
                'truncated' => $note['truncated'],
            ];
        }

        // 结构化注入：插入点写批注标记（不是 HTML），渲染时变成模板节点，各格式自己决定怎么显示
        $layout = AnnotationLayout::apply(
            $content,
            $glossaries,
            $issues,
            $notes,
            true,
            $assessment === null ? null : ['id' => $assessment['id'], 'score' => $assessment['score']],
            $annotations,
        );
        $result['content'] = $layout['content'];
        $result['notes'] = array_map(fn ($id) => $footnotes[$id], $layout['notes']);
        $result['assessed'] = $assessment !== null;

        return $result;
    }

    /**
     * 把一条批注（commentary / note）整理成要插入正文的 note 模板（正文 + 参数）与段后脚注条目。
     * 不合格的（正文为空、commentary 不是句子模板）返回 null，不插角标也不进脚注列表。
     *
     * 脚注条目的正文（纯文本）不在这里渲染：`text` 是待渲染的源码，`truncated` 表示义注被截断、
     * 渲染后要补省略号。整块一起渲染见 {@see self::renderFootnoteTexts()}。
     *
     * @return array{body: string, params: array<string, string>, text: array{source: string, channels: list<string>, contentType: string}, truncated: bool, citeHtml: string, order: array<int, int>}|null
     */
    protected function renderAnnotationNote(Discussion $note, Sentence $row): ?array
    {
        $noteContent = trim((string) $note->content);
        if ($noteContent === '') {
            return null;
        }
        $sents = [];
        if ($note->type === Discussion::TYPE_COMMENTARY) {
            // content 是一个或多个义注句子模板 {{book-para-start-end}}（一个词可能由义注
            // 多句解释，按顺序并列，如 {{135-404-50-54}}{{135-404-55-65}}）。
            if (! preg_match(Discussion::COMMENTARY_PATTERN, $noteContent)) {
                return null;
            }
            preg_match_all('/\{\{(\d+)-(\d+)-(\d+)-(\d+)\}\}/', $noteContent, $sents, PREG_SET_ORDER);
            // 义注正文只需译文（不要巴利原文）：把每个裸句模板 {{book-para-start-end}}
            // 转成 {{sent|id=…|text=translation}}，让 sent 模板只输出 translation。
            // 句子可能有很多条，只渲染前 MAX_COMMENTARY_SENTENCES 句；
            // 跳转锚点仍指向第一句，点进去能看到完整义注。
            $source = implode(' ', array_map(
                fn ($s) => '{{sent|id='.$s[1].'-'.$s[2].'-'.$s[3].'-'.$s[4].'|text=translation}}',
                array_slice($sents, 0, self::MAX_COMMENTARY_SENTENCES)
            ));
        } else {
            // 普通边注：content 就是注解正文，照原样渲染。
            $source = $noteContent;
        }
        // 段后脚注列表用的纯文本（边注本身是结构化的 note 节点，正文照 markdown 渲染）
        $text = [
            'source' => $source,
            'channels' => [(string) $row->channel_uid],
            'contentType' => $note->effectiveContentType(),
        ];
        // 义注被截断时补省略号，提示后面还有；想看全文点 <cite> 跳过去。
        $truncated = count($sents) > self::MAX_COMMENTARY_SENTENCES;
        if ($truncated) {
            $source .= '...';
        }
        // 出处只有 commentary 有。跳转目标（义注书-段-起-止）同时挂在 <cite> 和角标
        // <label> 上，前者用于「点链接跳页」，后者用于平板双栏「点角标跨栏高亮」。
        // 多句时跳转到第一句（义注对这个词的解释从那里开始）。
        $citeHtml = '';
        $params = [];
        if ($sents) {
            $m = $sents[0];
            $target = ' data-book="'.$m[1].'" data-para="'.$m[2].'" data-start="'.$m[3].'" data-end="'.$m[4].'"';
            $citeHtml = '<cite class="anno-jump"'.$target.'>义注</cite>';
            $params = ['cite' => '义注', 'citelink' => $m[1].'-'.$m[2].'-'.$m[3].'-'.$m[4]];
        }

        return [
            // note 模板的正文（markdown）与参数：渲染成 tufte sidenote（label + input + span.sidenote）
            'body' => $source,
            'params' => $params,
            'text' => $text,
            'truncated' => $truncated,
            'citeHtml' => $citeHtml,
            // 同一插入点上的先后：按义注原文的先后；普通 note 没有坐标，取 0，排在义注对应之前
            'order' => $sents ? [(int) $sents[0][1], (int) $sents[0][2], (int) $sents[0][3]] : [0, 0, 0],
        ];
    }

    /**
     * 段落脚注列表：把本段的注释条目逐条列在段后，每条默认收起为可配置行数（见阅读器 CSS）。
     *
     * @param  list<array{fnId: string, noteHtml: string, citeHtml: string}>  $notes
     */
    protected function renderFootnoteList(array $notes): string
    {
        if (empty($notes)) {
            return '';
        }
        $html = '<div class="anno-footnotes">';
        foreach ($notes as $note) {
            $html .= '<div class="anno-footnote">'
                .'<input type="checkbox" id="'.$note['fnId'].'" class="anno-fn-toggle"/>'
                .'<label for="'.$note['fnId'].'" class="anno-fn-label">'
                .'<span class="anno-fn-body">'.e($note['noteHtml']).'</span>'
                .'</label>'
                .$note['citeHtml']
                .'</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * 一次查回这些句子上要注入阅读页的批注，按句子 uid 分组、组内按创建时间升序。
     *
     * @param  Collection<int, Sentence>  $records
     * @return array<string, list<Discussion>>
     */
    protected function readingAnnotations(Collection $records): array
    {
        $grouped = [];
        foreach ($records->pluck('uid')->unique()->chunk(1000) as $uids) {
            Discussion::where('res_type', 'sentence')
                ->whereIn('res_id', $uids->values()->all())
                ->whereIn('type', Discussion::READING_TYPES)
                // 只注入话题本身；回复（类型跟随话题）不进阅读页
                ->whereNull('parent')
                ->where(fn ($q) => $q->whereIn('type', Discussion::STATELESS_TYPES)->orWhere('status', 'active'))
                ->orderBy('created_at')
                ->get()
                ->each(function (Discussion $discussion) use (&$grouped) {
                    $grouped[(string) $discussion->res_id][] = $discussion;
                });
        }

        return $grouped;
    }

    /**
     * 整块的段后脚注正文（纯文本）一次渲染：批注里的术语、义注句子合在一起查。
     *
     * @param  array<int, array{notes: list<array<string, mixed>>}>  $injections
     * @return array<string, string> "句序号:脚注序号" => 纯文本
     */
    protected function renderFootnoteTexts(array $injections, string $channelType): array
    {
        $items = [];
        foreach ($injections as $i => $injected) {
            foreach ($injected['notes'] as $n => $note) {
                $items["{$i}:{$n}"] = $note['text'];
            }
        }
        if ($items === []) {
            return [];
        }
        $texts = app(MarkupService::class)->renderMany($items, new RenderOptions(format: Format::Text, channelType: $channelType));
        foreach ($injections as $i => $injected) {
            foreach ($injected['notes'] as $n => $note) {
                $text = (string) ($texts["{$i}:{$n}"] ?? '');
                $texts["{$i}:{$n}"] = $note['truncated'] ? rtrim($text).'...' : $text;
            }
        }

        return $texts;
    }

    /**
     * 渲染同一本书的若干段落（没命中缓存的那些），结果含段落外壳，可直接缓存。
     *
     * 整块只查三次库：channel、句子（按段、word_start 排序）、批注；全部句子一次交给 Markup 渲染，
     * 脚注正文再一次。标题级别与段落 class 取自 {@see PaliTextService}（缓存的经文静态数据）。
     *
     * @param  list<int>  $paras
     * @return array<int, array{para: int, display: string, sentences: list<array{sid: string, html: string}>}>
     */
    protected function renderReadParagraphs(int $book, array $paras, string $channelUid, string $format): array
    {
        $results = [];
        foreach ($paras as $para) {
            $results[$para] = ['para' => $para, 'display' => '', 'sentences' => []];
        }
        $channel = Channel::where('uid', $channelUid)
            ->select(['uid', 'type', 'lang', 'name'])->first();
        if (! $channel) {
            return $results;
        }
        $isOrigin = $channel->type === 'original' || $channel->type === 'wbw';
        $channelType = $channel->type === 'nissaya' ? 'nissaya' : 'translation';
        $area = $isOrigin ? 'original' : 'translation';

        $records = Sentence::select($this->selectCol)
            ->where('book_id', $book)
            ->where('channel_uid', $channelUid)
            ->whereIn('paragraph', $paras)
            ->orderBy('paragraph')
            ->orderBy('word_start')
            ->get()
            ->values();
        if ($records->isEmpty()) {
            return $results;
        }

        $annotations = $this->readingAnnotations($records);
        $injections = [];
        $assessedParas = [];
        foreach ($records as $i => $row) {
            $injections[$i] = $this->injectAnnotationNotes($row, $channelType, $annotations[(string) $row->uid] ?? []);
            if ($injections[$i]['assessed']) {
                $assessedParas[(int) $row->paragraph] = true;
            }
        }

        $sources = [];
        foreach ($records as $i => $row) {
            $injected = $injections[$i];
            $content = $injected['content'];
            // 段里有句子评估过，其余没评估的句子在句尾补灰色对勾：不然读者会把它与下一句的
            // 对勾连起来，误以为是一句。整段都没评估就都不补（段落本身有分隔）。按段算，不是按整块
            if (isset($assessedParas[(int) $row->paragraph]) && ! $injected['assessed'] && trim($content) !== '') {
                $content .= $injected['annotations']->unassessedMark();
            }
            $sources[$i] = [
                'source' => $content,
                'channels' => [(string) $row->channel_uid],
                'contentType' => (string) $row->content_type,
                'annotations' => $injected['annotations'],
                // 边注的 HTML id 带上句子坐标，整页唯一
                'idPrefix' => "{$row->book_id}-{$row->paragraph}-{$row->word_start}-{$row->word_end}",
            ];
        }
        // 整块一起渲染：各句里的术语、句子模板（含义注对应）合在一起批量查
        $rendered = app(MarkupService::class)->renderMany(
            $sources,
            // markdown 每句末尾附自己的脚注正文（`[^n]: …`）。编号每句从 1 起，跨句会重号，
            // 整章导出时由 V3\TipitakaExportBuilder 逐句重新编号；不附正文的话 `[^n]` 无处可指
            new RenderOptions(format: self::markupFormat($format), channelType: $channelType, footnote: $format === 'markdown'),
        );
        // 段后脚注列表只有 html 有
        $noteTexts = $format === 'html' ? $this->renderFootnoteTexts($injections, $channelType) : [];

        $display = [];
        $notes = [];
        foreach ($records as $i => $row) {
            $para = (int) $row->paragraph;
            foreach ($injections[$i]['notes'] as $n => $note) {
                $notes[$para][] = [
                    'fnId' => $note['fnId'],
                    'noteHtml' => $noteTexts["{$i}:{$n}"] ?? '',
                    'citeHtml' => $note['citeHtml'],
                ];
            }
            $html = $rendered[$i] ?? '';
            if (empty($html)) {
                continue;
            }
            $sid = "{$row->book_id}-{$row->paragraph}-{$row->word_start}-{$row->word_end}";
            $results[$para]['sentences'][] = ['sid' => $sid, 'html' => $html];
            if ($format === 'html') {
                $class = $isOrigin ? 'sentence origin' : 'sentence';
                $display[$para][] = "<div class='{$class}' lang='{$channel->lang}' data-sid='{$sid}'>{$html}</div>";
            } else {
                $display[$para][] = $html;
            }
        }

        $paliTexts = app(PaliTextService::class);
        foreach ($display as $para => $lines) {
            if ($format !== 'html') {
                // 其他格式一行一句
                $results[$para]['display'] = implode("\n", $lines);

                continue;
            }
            // html 格式加段落外壳；段后追加段落脚注列表（本段义注/复注条目）
            $content = implode('', $lines);
            $level = $paliTexts->headingLevel($book, $para);
            $pClass = $paliTexts->paragraph($book, $para)['class'] ?? '';
            $inner = $level > 0 ? "<h{$level}>{$content}</h{$level}>" : "<div class='para-block'>{$content}</div>";
            $footnoteList = $this->renderFootnoteList($notes[$para] ?? []);
            $results[$para]['display'] = "<div id='para-{$para}' class='{$area} {$pClass}' data-para='{$para}'>{$inner}{$footnoteList}</div>";
        }

        return $results;
    }
}
