<?php

namespace App\Http\Api;

use App\Models\Task;
use App\Models\PaliText;
use App\Models\PaliSentence;
use App\Models\AiModel;
use App\Models\Sentence;

use App\Http\Api\Mq;
use App\Http\Api\ChannelApi;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;

class AiTaskPrepare
{
    /**
     * 读取task信息，将任务拆解为单句小任务
     *
     * @param  string  $taskId 任务uuid
     * @return array 拆解后的提示词数组
     */
    public static function translate(string $taskId, bool $send = true)
    {
        $task = Task::findOrFail($taskId);
        $description = $task->description;
        $rows = explode("\n", $description);
        $params = [];
        foreach ($rows as $key => $row) {
            if (strpos($row, '=') !== false) {
                $param = explode('=', trim($row, '|'));
                $params[$param[0]] = $param[1];
            }
        }
        if (!isset($params['type'])) {
            Log::error('no $params.type');
            return false;
        }

        //get sentences in article
        $sentences = array();
        $totalLen = 0;
        switch ($params['type']) {
            case 'sentence':
                if (!isset($params['id'])) {
                    Log::error('no $params.id');
                    return false;
                }
                $sentences[] = explode('-', $params['id']);
                break;
            case 'para':
                if (!isset($params['book']) || !isset($params['paragraphs'])) {
                    Log::error('no $params.book or paragraphs');
                    return false;
                }
                $sent = PaliSentence::where('book', $params['book'])
                    ->where('paragraph', $params['paragraphs'])->orderBy('word_begin')->get();
                foreach ($sent as $key => $value) {
                    $sentences[] = [
                        'id' => [
                            $value->book,
                            $value->paragraph,
                            $value->word_begin,
                            $value->word_end,
                        ],
                        'strlen' => $value->length
                    ];
                    $totalLen += $value->length;
                }
                break;
            case 'chapter':
                if (!isset($params['book']) || !isset($params['paragraphs'])) {
                    Log::error('no $params.book or paragraphs');
                    return false;
                }
                $chapterLen = PaliText::where('book', $params['book'])
                    ->where('paragraph', $params['paragraphs'])->value('chapter_len');
                $sent = PaliSentence::where('book', $params['book'])
                    ->whereBetween('paragraph', [$params['paragraphs'], $params['paragraphs'] + $chapterLen - 1])
                    ->orderBy('paragraph')
                    ->orderBy('word_begin')->get();
                foreach ($sent as $key => $value) {
                    $sentences[] = [
                        'id' => [
                            $value->book,
                            $value->paragraph,
                            $value->word_begin,
                            $value->word_end,
                        ],
                        'strlen' => $value->length
                    ];
                    $totalLen += $value->length;
                }
                break;
            default:
                return false;
                break;
        }

        //render prompt
        $mdRender = new MdRender([
            'format' => 'prompt',
            'footnote' => false,
            'paragraph' => false,
        ]);
        $m = new \Mustache_Engine(array(
            'entity_flags' => ENT_QUOTES,
            'escape' => function ($value) {
                return $value;
            }
        ));

        # ai model
        $aiModel = AiModel::findOrFail($task->executor_id);
        $modelToken = AuthController::getUserToken($aiModel->uid);
        $aiModel['token'] = $modelToken;
        $sumLen = 0;
        $mqData = [];
        foreach ($sentences as $key => $sentence) {
            $sumLen += $sentence['strlen'];
            $sid = implode('-', $sentence['id']);
            Log::debug($sid);
            $sentChannelInfo = explode('@', $params['channel']);
            $channelId = $sentChannelInfo[0];
            $data = [];
            $data['origin'] = '{{' . $sid . '}}';
            $data['translation'] = '{{sent|id=' . $sid;
            $data['translation'] .= '|channel=' . $channelId;
            $data['translation'] .= '|text=translation}}';
            if (isset($params['nissaya']) && !empty($params['nissaya'])) {
                $nissayaChannel = explode('@', $params['nissaya']);
                $channelInfo = ChannelApi::getById($nissayaChannel[0]);
                if ($channelInfo) {
                    //查看句子是否存在
                    $nissayaSent = Sentence::where('book_id', $sentence['id'][0])
                        ->where('paragraph', $sentence['id'][1])
                        ->where('word_start', $sentence['id'][2])
                        ->where('word_end', $sentence['id'][3])
                        ->where('channel_uid', $nissayaChannel[0])->first();
                    if ($nissayaSent && !empty($nissayaSent->content)) {
                        $nissayaData = [];
                        $nissayaData['channel'] = $channelInfo;
                        $nissayaData['data'] = '{{sent|id=' . $sid;
                        $nissayaData['data'] .= '|channel=' . $nissayaChannel[0];
                        $nissayaData['data'] .= '|text=translation}}';
                        $data['nissaya'] = $nissayaData;
                    }
                }
            }

            //Log::debug('mustache render', ['tpl' => $description, 'data' => $data]);
            $content = $m->render($description, $data);
            $prompt = $mdRender->convert($content, []);
            //gen mq
            $aiMqData = [
                'model' => $aiModel,
                'task' => [
                    'info' => $task,
                    'progress' => [
                        'current' => $sumLen,
                        'total' => $totalLen
                    ],
                ],
                'prompt' => $prompt,
                'sentence' => [
                    'book_id' => $sentence['id'][0],
                    'paragraph' => $sentence['id'][1],
                    'word_start' => $sentence['id'][2],
                    'word_end' => $sentence['id'][3],
                    'channel_uid' => $channelId,
                    'content' => $prompt,
                    'content_type' => 'markdown',
                    'access_token' => $sentChannelInfo[1] ?? $params['token'],
                ],
            ];
            array_push($mqData, $aiMqData);
        }
        if ($send) {
            Mq::publish('ai_translate', $mqData);
        }
        return $mqData;
    }
}
