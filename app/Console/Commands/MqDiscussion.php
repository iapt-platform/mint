<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\WebHook;
use App\Models\Discussion;
use App\Models\Article;
use App\Http\Api\Mq;
use App\Tools\WebHook as WebHookSend;
use App\Http\Api\MdRender;
use App\Http\Api\UserApi;
use Illuminate\Support\Facades\Log;

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $exchange = 'router';
        $queue = 'discussion';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Log::info("discussion worker start .");
        Mq::worker($exchange,$queue,function ($message){
            Log::info('mq discussion receive {message}',['message'=>json_encode($message,JSON_UNESCAPED_UNICODE)]);
            $result = 0;
            switch ($message->res_type) {
                case 'sentence':
                    $sentence = Sentence::where('uid',$message->res_id)->first();
                    if(!$sentence){
                        return 0;
                    }
                    $contentHtml = MdRender::render($sentence->content,
                                             [$sentence->channel_uid],
                                             null,
                                             'read',
                                             'translation',
                                             $sentence->content_type);
                    $contentTxt = strip_tags($contentHtml);
                    /**生成消息内容 */

                    $msgParam = array();
                    $msgParam['anchor-content'] = $contentTxt;
                    $msgParam['nickname'] = $message->editor->nickName;
                    $link = config('app.url')."/pcd/discussion/topic/";
                    if($message->parent){
                        $msgParam['topic-title'] = Discussion::where('id',$message->parent)->value('title');
                        $id = $message->id;
                        $msgParam['link'] = $link . $message->parent.'#'.$id;
                        $msgTitle = "回复讨论";
                        $type = 'reply';
                    }else{
                        $msgParam['title'] = $message->title;
                        $msgParam['link'] = $link . $message->id;
                        $msgTitle = "创建讨论";
                        $type = 'create';
                    }
                    if($message->content){
                        $msgParam['content'] = $message->content;
                    }

                    $rootId = UserApi::getById(0)['uid'];
                    $articleTitle = "webhook://discussion/{$type}/zh-hans";
                    $tpl = Article::where('owner',$rootId)
                                  ->where('title',$articleTitle)
                                  ->value('content');
                    if(empty($tpl)){
                        Log::error('mq:discussion 模版不能为空',['tpl_title'=>$articleTitle]);
                        return 1;
                    }
                    $m = new \Mustache_Engine(array('entity_flags'=>ENT_QUOTES,
                                                'delimiters' => '{% %}',));
                    $msgContent = $m->render($tpl,$msgParam);

                    $webhooks = WebHook::where('res_id',$sentence->channel_uid)
                                    ->where('status','active')
                                    ->get();
                    foreach ($webhooks as $key => $hook) {
                        $event = json_decode($hook->event);

                        if(is_array($event)){
                            if(!in_array('discussion',$event)){
                                continue;
                            }
                        }else{
                            continue;
                        }
                        $command = '';
                        $whSend = new WebHookSend;
                        $ok = 0;
                        switch ($hook->receiver) {
                            case 'dingtalk':
                                $ok = $whSend->dingtalk($hook->url,$msgTitle,$msgContent);
                                break;
                            case 'wechat':
                                $ok = $whSend->wechat($hook->url,null,$msgContent);
                                break;
                            default:
                                $ok=2;
                                break;
                        }
                        $result += $ok;
                        $logMsg = "{$command}  ok={$ok}";
                        if($ok === 0){
                            $this->info($logMsg);
                        }else{
                            $this->error($logMsg);
                        }

                        if($ok === 0){
                            Log::debug('mq:discussion: send success {url}',['url'=>$hook->url]);
                            WebHook::where('id',$hook->id)->increment('success');
                        }else{
                            Log::error('mq:discussion: send fail {url}',['url'=>$hook->url]);
                            WebHook::where('id',$hook->id)->increment('fail');
                        }
                    }
                    break;
                default:
                    # code...
                    break;
            }
            return $result;
        });

        return 0;
    }
}
