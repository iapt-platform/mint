<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Tools\RedisClusters;

use App\Models\Task;
use App\Models\PaliText;
use App\Models\PaliSentence;
use App\Models\AiModel;
use App\Models\Sentence;

use App\Http\Api\ChannelApi;

use App\Http\Controllers\AuthController;

use App\Http\Api\MdRender;
use App\Exceptions\SectionTimeoutException;

class DatabaseException extends \Exception {}

class AiTranslateService
{
    private $queue = 'ai_translate';
    private $modelToken = null;
    private $task = null;
    protected $mq;
    private $apiTimeout = 30;
    private $llmTimeout = 300;
    private $taskTopicId;
    private $stop = false;
    private $maxProcessTime = 15 * 60; //一个句子的最大处理时间
    private $mqTimeout = 60;

    public function __construct() {}

    /**
     * @param string $messageId
     * @param array $translateData
     */
    public function processTranslate(string $messageId, array $messages): bool
    {
        $start = time();

        if (!is_array($messages) || count($messages) === 0) {
            Log::error('message is not array');
            return false;
        }


        $first = $messages[0];
        $this->task = $first->task->info;
        $taskId = $this->task->id;
        RedisClusters::put("/task/{$taskId}/message_id", $messageId);
        $pointerKey = "/task/{$taskId}/pointer";
        $pointer = 0;
        if (RedisClusters::has($pointerKey)) {
            //回到上次中断的点
            $pointer = RedisClusters::get($pointerKey);
            Log::info("last break point {$pointer}");
        }

        //获取model token
        $this->modelToken = $first->model->token;
        Log::debug($this->queue . ' ai assistant token', ['token' => $this->modelToken]);

        $this->setTaskStatus($this->task->id, 'running');

        // 设置task discussion topic
        $this->taskTopicId = $this->taskDiscussion(
            $this->task->id,
            'task',
            $this->task->title,
            $this->task->category,
            null
        );

        $time = [$this->maxProcessTime];
        for ($i = $pointer; $i < count($messages); $i++) {
            // 获取当前内存使用量
            Log::debug("memory usage: " . memory_get_usage(true) / 1024 / 1024 . " MB");
            // 获取峰值内存使用量
            Log::debug("memory peak usage: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB");

            if ($this->stop) {
                Log::info("收到退出信号 pointer={$i}");
                return false;
            }
            if (\App\Tools\Tools::isStop()) {
                //检测到停止标记
                return false;
            }

            RedisClusters::put($pointerKey, $i);
            $message = $messages[$i];
            $taskDiscussionContent = [];

            //推理
            try {
                $responseLLM = $this->requestLLM($message);
                $taskDiscussionContent[] = '- LLM request successful';
            } catch (RequestException $e) {
                throw $e;
            }


            if ($this->task->category === 'translate') {
                //写入句子库
                $message->sentence->content = $responseLLM['content'];
                try {
                    $this->saveSentence($message->sentence);
                } catch (\Exception $e) {
                    Log::error('sentence', ['message' => $e]);
                    continue;
                }
            }
            if ($this->task->category === 'suggest') {
                //写入pr
                try {
                    $this->savePr($message->sentence, $responseLLM['content']);
                } catch (\Exception $e) {
                    Log::error('sentence', ['message' => $e]);
                    continue;
                }
            }

            #获取句子id
            $sUid = $this->getSentenceId($message->sentence);

            //写入句子 discussion
            $topicId = $this->taskDiscussion(
                $sUid,
                'sentence',
                $this->task->title,
                $this->task->category,
                null
            );

            if ($topicId) {
                Log::info($this->queue . ' discussion create topic successful');
                $data['parent'] = $topicId;
                unset($data['title']);
                $topicChildren = [];
                //提示词
                $topicChildren[] = $message->prompt;
                //任务结果
                $topicChildren[] = $responseLLM['content'];
                //推理过程写入discussion
                if (
                    isset($responseLLM['reasoningContent']) &&
                    !empty($responseLLM['reasoningContent'])
                ) {
                    $topicChildren[] = $responseLLM['reasoningContent'];
                }
                foreach ($topicChildren as  $content) {
                    Log::debug($this->queue . ' discussion child request', ['data' => $data]);

                    $dId = $this->taskDiscussion($sUid, 'sentence', $this->task->title, $content, $topicId);
                    if ($dId) {
                        Log::info($this->queue . ' discussion child successful');
                    }
                }
            } else {
                Log::error($this->queue . ' discussion create topic response is null');
            }


            //修改task 完成度
            $progress = $this->setTaskProgress($message->task->progress);
            $taskDiscussionContent[] = "- progress=" . $progress;
            //写入task discussion
            if ($this->taskTopicId) {
                $content = implode('\n', $taskDiscussionContent);
                $dId = $this->taskDiscussion(
                    $this->task->id,
                    'task',
                    $this->task->title,
                    $content,
                    $this->taskTopicId
                );
            } else {
                Log::error('no task discussion root');
            }

            //计算剩余时间是否足够再做一次
            $time[] = time() - $start;
            rsort($time);
            $remain = $this->mqTimeout - (time() - $start);
            if ($remain < $time[0]) {
                throw new SectionTimeoutException;
            }
        }
        //任务完成 修改任务状态为 done
        if ($i === count($messages)) {
            $this->setTaskStatus($this->task->id, 'done');
            RedisClusters::forget($pointerKey);
            Log::info('ai translate task complete');
        }

        return true;
    }
    private function setTaskStatus($taskId, $status)
    {
        $url = config('app.url') . '/api/v2/task-status/' . $taskId;
        $data = [
            'status' => $status,
        ];
        Log::debug('ai_translate task status request', ['url' => $url, 'data' => $data]);
        $response = Http::timeout($this->apiTimeout)->withToken($this->modelToken)->patch($url, $data);
        //判断状态码
        if ($response->failed()) {
            Log::error('ai_translate task status error', ['data' => $response->json()]);
        } else {
            Log::info('ai_translate task status done');
        }
    }

    private function saveModelLog($token, $data)
    {
        $url = config('app.url') . '/api/v2/model-log';

        $response = Http::timeout($this->apiTimeout)->withToken($token)->post($url, $data);
        if ($response->failed()) {
            Log::error('ai-translate model log create failed', ['data' => $response->json()]);
            return false;
        }
        return true;
    }

    private function taskDiscussion($resId, $resType, $title, $content, $parentId = null)
    {
        $url = config('app.url') . '/api/v2/discussion';
        $taskDiscussionData = [
            'res_id' => $resId,
            'res_type' => $resType,
            'content' => $content,
            'content_type' => 'markdown',
            'type' => 'discussion',
            'notification' => false,
        ];
        if ($parentId) {
            $taskDiscussionData['parent'] = $parentId;
        } else {
            $taskDiscussionData['title'] = $title;
        }
        Log::debug($this->queue . ' discussion create', ['url' => $url, 'data' => json_encode($taskDiscussionData)]);

        $response = Http::timeout($this->apiTimeout)
            ->withToken($this->modelToken)
            ->post($url, $taskDiscussionData);
        if ($response->failed()) {
            Log::error($this->queue . ' discussion create error', ['data' => $response->json()]);
            return false;
        }
        Log::debug($this->queue . ' discussion create', ['data' => json_encode($response->json())]);

        if (isset($response->json()['data']['id'])) {
            return $response->json()['data']['id'];
        }
        return false;
    }

    private function requestLLM($message)
    {
        $param = [
            "model" => $message->model->model,
            "messages" => [
                ["role" => "system", "content" => $message->model->system_prompt ?? ''],
                ["role" => "user", "content" => $message->prompt],
            ],
            "temperature" => 0.7,
            "stream" => false
        ];
        Log::info($this->queue . ' LLM request ' . $message->model->url . ' model:' . $param['model']);
        Log::debug($this->queue . ' LLM api request', [
            'url' => $message->model->url,
            'data' => json_encode($param),
        ]);

        //写入 model log
        $modelLogData = [
            'model_id' => $message->model->uid,
            'request_at' => now(),
            'request_data' => json_encode($param, JSON_UNESCAPED_UNICODE),
        ];
        //失败重试
        $maxRetries = 3;
        $attempt = 0;
        try {
            while ($attempt < $maxRetries) {
                try {
                    $response = Http::withToken($message->model->key)
                        ->timeout($this->llmTimeout)
                        ->post($message->model->url, $param);

                    // 如果状态码是 4xx 或 5xx，会自动抛出 RequestException
                    $response->throw();

                    Log::info($this->queue . ' LLM request successful');

                    $modelLogData['request_headers'] = json_encode($response->handlerStats(), JSON_UNESCAPED_UNICODE);
                    $modelLogData['response_headers'] = json_encode($response->headers(), JSON_UNESCAPED_UNICODE);
                    $modelLogData['status'] = $response->status();
                    $modelLogData['response_data'] = json_encode($response->json(), JSON_UNESCAPED_UNICODE);
                    self::saveModelLog($this->modelToken, $modelLogData);
                    break; // 跳出 while 循环
                } catch (RequestException $e) {
                    $attempt++;
                    $status = $e->response->status();

                    // 某些错误不需要重试
                    if (in_array($status, [400, 401, 403, 404, 422])) {
                        Log::warning("客户端错误，不重试: {$status}\n");
                        throw $e; // 重新抛出异常
                    }
                    // 服务器错误或网络错误可以重试
                    if ($attempt < $maxRetries) {
                        $delay = pow(2, $attempt); // 指数退避
                        Log::warning("请求失败（第 {$attempt} 次），{$delay} 秒后重试...\n");
                        sleep($delay);
                    } else {
                        Log::error("达到最大重试次数，请求最终失败\n");
                        throw $e;
                    }
                }
            }
        } catch (RequestException $e) {
            Log::error($this->queue . ' LLM request exception: ' . $e->getMessage());
            $failResponse = $e->response;
            $modelLogData['request_headers'] = json_encode($failResponse->handlerStats(), JSON_UNESCAPED_UNICODE);
            $modelLogData['response_headers'] = json_encode($failResponse->headers(), JSON_UNESCAPED_UNICODE);
            $modelLogData['status'] = $failResponse->status();
            $modelLogData['response_data'] = $response->body();
            $modelLogData['success'] = false;
            self::saveModelLog($this->modelToken, $modelLogData);
            throw $e;
        }

        Log::info($this->queue . ' model log saved');

        $aiData = $response->json();
        Log::debug($this->queue . ' LLM http response', ['data' => $response->json()]);
        $responseContent = $aiData['choices'][0]['message']['content'];
        if (isset($aiData['choices'][0]['message']['reasoning_content'])) {
            $reasoningContent = $aiData['choices'][0]['message']['reasoning_content'];
        }
        $output = ['content' => $responseContent];
        Log::debug($this->queue . ' LLM response content=' . $responseContent);
        if (empty($reasoningContent)) {
            Log::debug($this->queue . ' no reasoningContent');
        } else {
            Log::debug($this->queue . ' reasoning=' . $reasoningContent);
            $output['reasoningContent'] = $reasoningContent;
        }

        return $output;
    }

    /**
     * 写入句子库
     */
    private function saveSentence($sentence)
    {
        $url = config('app.url') . '/api/v2/sentence';

        Log::info($this->queue . " sentence update {$url}");
        $response = Http::timeout($this->apiTimeout)->withToken($this->modelToken)->post($url, [
            'sentences' => [$sentence],
        ]);
        if ($response->failed()) {
            Log::error($this->queue . ' sentence update failed', [
                'url' => $url,
                'data' => $response->json(),
            ]);
            throw new DatabaseException("sentence 数据库写入错误");
        }
        $count = $response->json()['data']['count'];
        Log::info("{$this->queue} sentence update {$count} successful");
    }

    private function savePr($sentence, $content)
    {
        $url = config('app.url') . '/api/v2/sentpr';
        Log::info($this->queue . " sentence update {$url}");
        $response = Http::timeout($this->apiTimeout)->withToken($this->modelToken)->post($url, [
            'book' => $sentence->book_id,
            'para' => $sentence->paragraph,
            'begin' => $sentence->word_start,
            'end' => $sentence->word_end,
            'channel' => $sentence->channel_uid,
            'text' => $content,
            'notification' => false,
            'webhook' => false,
        ]);
        if ($response->failed()) {
            Log::error($this->queue . ' sentence update failed', [
                'url' => $url,
                'data' => $response->json(),
            ]);
            throw new DatabaseException("pr 数据库写入错误");
        }
        if ($response->json()['ok']) {
            Log::info("{$this->queue} sentence suggest update successful");
        } else {
            Log::error("{$this->queue} sentence suggest update failed", [
                'url' => $url,
                'data' => $response->json(),
            ]);
        }
    }

    private function getSentenceId($sentence)
    {
        $url = config('app.url') . '/api/v2/sentence-info/aa';
        Log::info('ai translate', ['url' => $url]);
        $response = Http::timeout($this->apiTimeout)->withToken($this->modelToken)->get($url, [
            'book' => $sentence->book_id,
            'par' => $sentence->paragraph,
            'start' => $sentence->word_start,
            'end' => $sentence->word_end,
            'channel' => $sentence->channel_uid
        ]);
        if (!$response->json()['ok']) {
            Log::error($this->queue . ' sentence id error', ['data' => $response->json()]);
            return false;
        }
        $sUid = $response->json()['data']['id'];
        Log::debug("sentence id={$sUid}");
        return $sUid;
    }

    private function setTaskProgress($current)
    {
        $taskProgress = $current;
        if ($taskProgress->total > 0) {
            $progress = (int)($taskProgress->current * 100 / $taskProgress->total);
        } else {
            $progress = 100;
            Log::error($this->queue . ' progress total is zero', ['task_id' => $this->task->id]);
        }

        $url = config('app.url') . '/api/v2/task/' . $this->task->id;
        $data = [
            'progress' => $progress,
        ];
        Log::debug($this->queue . ' task progress request', ['url' => $url, 'data' => $data]);
        $response = Http::timeout($this->apiTimeout)->withToken($this->modelToken)->patch($url, $data);
        if ($response->failed()) {
            Log::error($this->queue . ' task progress error', ['data' => $response->json()]);
        } else {

            Log::info($this->queue . ' task progress successful progress=' . $response->json()['data']['progress']);
        }
        return $progress;
    }
    public function handleFailedTranslate(string $messageId, array $translateData, \Exception $exception): void
    {
        try {
            // 彻底失败时的业务逻辑
            // 设置task为失败状态
            $this->setTaskStatus($this->task->id, 'stop');
            //将故障信息写入task discussion
            if ($this->taskTopicId) {
                $dId = $this->taskDiscussion(
                    $this->task->id,
                    'task',
                    $this->task->title,
                    "**处理失败ai任务时出错** 请重启任务 message id={$messageId} 错误信息：" . $exception->getMessage(),
                    $this->taskTopicId
                );
            }
        } catch (\Exception $e) {
            Log::error('处理失败ai任务时出错', ['error' => $e->getMessage()]);
        }
    }

    /**
     * 读取task信息，将任务拆解为单句小任务
     *
     * @param  string  $taskId 任务uuid
     * @return array 拆解后的提示词数组
     */
    public function makeByTask(string $taskId, $aiAssistantId, bool $send = true)
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
        $aiModel = AiModel::findOrFail($aiAssistantId);
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
            $mq = app(RabbitMQService::class);
            $mq->publishMessage('ai_translate', $mqData);
        }
        return $mqData;
    }
    public function stop()
    {
        $this->stop = true;
    }
}
