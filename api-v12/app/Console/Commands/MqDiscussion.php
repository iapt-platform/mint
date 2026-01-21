<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use App\Models\Sentence;
use App\Models\WebHook;
use App\Models\Discussion;
use App\Models\Article;
use App\Models\DhammaTerm;
use App\Models\Wbw;
use App\Models\WbwBlock;
use App\Http\Api\Mq;
use App\Tools\WebHook as WebHookSend;
use App\Http\Api\MdRender;
use App\Http\Api\UserApi;
use App\Http\Controllers\NotificationController;


class MqDiscussion extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:discussion
     * @var string
     */
    protected $signature = 'mq:discussion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (\App\Tools\Tools::isStop()) {
            return 0;
        }
        $exchange = 'router';
        $queue = 'discussion';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::info("discussion worker start .");
        Mq::worker($exchange, $queue, function ($message) {
            Log::info('mq discussion receive {message}', ['message' => json_encode($message, JSON_UNESCAPED_UNICODE)]);
            $result = 0;
            $msgParam = array();
            $msgParam['nickname'] = $message->editor->nickName;
            $link = config('app.url') . "/pcd/discussion/topic/";
            if ($message->parent) {
                $msgParam['topic-title'] = Discussion::where('id', $message->parent)->value('title');
                $id = $message->id;
                $msgParam['link'] = $link . $message->parent . '#' . $id;
                $msgParam['card_title'] = "回复讨论";
                $type = 'reply';
            } else {
                $msgParam['title'] = $message->title;
                $msgParam['link'] = $link . $message->id;
                $msgParam['card_title'] = "创建讨论";
                $type = 'create';
            }
            if ($message->content) {
                $msgParam['content'] = $message->content;
            }

            switch ($message->res_type) {
                case 'sentence':
                    $sentence = Sentence::where('uid', $message->res_id)->first();
                    if (!$sentence) {
                        Log::error('invalid sentence id ' . $message->res_id);
                        $result = 1;
                        break;
                    }

                    //站内信
                    try {
                        $sendTo = array();
                        //句子的channel拥有者
                        //$sendTo[] = $prData->channel->studio_id;
                        //句子的作者
                        if (!in_array($sentence->editor_uid, $sendTo)) {
                            $sendTo[] = $sentence->editor_uid;
                        }
                        //句子的采纳者
                        if (!empty($sentence->acceptor_uid) && !in_array($sentence->acceptor_uid, $sendTo)) {
                            $sendTo[] = $sentence->acceptor_uid;
                        }
                        $this->notification(
                            $message->editor->id,
                            $sendTo,
                            'discussion',
                            $message->id,
                            $sentence->channel_uid
                        );
                    } catch (\Exception $e) {
                        Log::error('send notification failed', ['exception' => $e]);
                    }

                    //webhook
                    $contentHtml = MdRender::render(
                        $sentence->content,
                        [$sentence->channel_uid],
                        null,
                        'read',
                        'translation',
                        $sentence->content_type
                    );
                    $contentTxt = strip_tags($contentHtml);
                    /**生成消息内容 */

                    $msgParam['anchor-content'] = $contentTxt;
                    $WebHookResId = $sentence->channel_uid;

                    $this->WebHook($msgParam, $type, $WebHookResId);
                    break;
                case 'wbw':
                    $wbw = Wbw::where('uid', $message->res_id)->first();
                    if (!$wbw) {
                        Log::error('invalid wbw id ' . $message->res_id);
                        $result = 1;
                        break;
                    }
                    $wbwBlock = WbwBlock::where('uid', $wbw->block_uid)->first();
                    if (!$wbwBlock) {
                        Log::error('invalid wbw-block id ' . $message->res_id);
                        $result = 1;
                        break;
                    }

                    //站内信
                    try {
                        $sendTo = array();
                        //channel拥有者
                        //$sendTo[] = $prData->channel->studio_id;
                        //作者
                        if (!in_array($wbw->creator_uid, $sendTo)) {
                            $sendTo[] = $wbw->creator_uid;
                        }
                        //提问者
                        if (!empty($message->parent)) {
                            $topicEditor = Discussion::where('id', $message->parent)
                                ->value('editor_uid');
                            if (!empty($topicEditor) && !in_array($topicEditor, $sendTo)) {
                                $sendTo[] = $topicEditor;
                                Log::debug('发送给提问者', ['data' => $topicEditor]);
                            }
                        }

                        $this->notification(
                            $message->editor->id,
                            $sendTo,
                            'discussion',
                            $message->id,
                            $wbwBlock->channel_uid
                        );
                    } catch (\Exception $e) {
                        Log::error('send notification failed', ['exception' => $e]);
                    }

                    $msgParam['anchor-content'] = $wbw->word;
                    $WebHookResId = $wbwBlock->channel_uid;
                    $this->WebHook($msgParam, $type, $WebHookResId);
                    break;
                case 'term':
                    $term = DhammaTerm::where('guid', $message->res_id)->first();
                    if (!$term) {
                        Log::error('invalid term id ' . $message->res_id);
                        $result = 1;
                        break;
                    }
                    if (empty($term->channal) || !Str::isUuid($term->channal)) {
                        break;
                    }


                    //站内信
                    try {
                        $sendTo = array();
                        //拥有者
                        $sendTo[] = $term->term;
                        //作者
                        $editor = UserApi::getById($term->editor_id);
                        if ($editor['id'] !== 0 && !in_array($editor['id'], $sendTo)) {
                            $sendTo[] = $editor['id'];
                        }
                        $this->notification(
                            $message->editor->id,
                            $sendTo,
                            'discussion',
                            $message->id,
                            $term->channal
                        );
                    } catch (\Exception $e) {
                        Log::error('send notification failed', ['exception' => $e]);
                    }
                    //webhook
                    $msgParam['anchor-content'] = $term->meaning . '(' . $term->word . ')';
                    $WebHookResId = $term->channal;
                    $this->WebHook($msgParam, 'term', $WebHookResId);

                    break;
                default:
                    # code...
                    break;
            }

            return $result;
        });

        return 0;
    }

    private function WebHook($msgParam, $type, $resId)
    {
        $rootId = UserApi::getById(0)['id'];
        $articleTitle = "webhook://discussion/{$type}/zh-hans";
        $tpl = Article::where('owner', $rootId)
            ->where('title', $articleTitle)
            ->value('content');
        if (empty($tpl)) {
            Log::error('mq:discussion 模版不能为空', ['tpl_title' => $articleTitle]);
            return 1;
        }
        $m = new \Mustache_Engine(array(
            'entity_flags' => ENT_QUOTES,
            'delimiters' => '{% %}',
        ));
        $msgContent = $m->render($tpl, $msgParam);

        $webhooks = WebHook::where('res_id', $resId)
            ->where('status', 'active')
            ->get();
        $result = 0;
        foreach ($webhooks as $key => $hook) {
            $event = json_decode($hook->event);

            if (is_array($event)) {
                if (!in_array('discussion', $event)) {
                    continue;
                }
            } else {
                continue;
            }
            $command = '';
            $whSend = new WebHookSend;
            $ok = 0;
            switch ($hook->receiver) {
                case 'dingtalk':
                    $ok = $whSend->dingtalk($hook->url, $msgParam['card_title'], $msgContent);
                    break;
                case 'wechat':
                    $ok = $whSend->wechat($hook->url, null, $msgContent);
                    break;
                default:
                    $ok = 2;
                    break;
            }
            $result += $ok;
            $logMsg = "{$command}  ok={$ok}";
            if ($ok === 0) {
                $this->info($logMsg);
            } else {
                $this->error($logMsg);
            }

            if ($ok === 0) {
                Log::debug('mq:discussion: send success {url}', ['url' => $hook->url]);
                WebHook::where('id', $hook->id)->increment('success');
            } else {
                Log::error('mq:discussion: send fail {url}', ['url' => $hook->url]);
                WebHook::where('id', $hook->id)->increment('fail');
            }
        }
    }

    private function notification($from, $to, $resType, $resId, $channel)
    {
        //发送站内信
        try {

            $sendCount = NotificationController::insert(
                $from,
                $to,
                $resType,
                $resId,
                $channel
            );
            $this->info("send notification success to [" . $sendCount . '] users');
        } catch (\Exception $e) {
            Log::error('send notification failed', ['exception' => $e]);
        }
        return;
    }
}
