<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\WebHook;
use App\Http\Api\Mq;

class MqDiscussion extends Command
{
    /**
     * The name and signature of the console command.
     *
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
        $exchange = 'router';
        $queue = 'discussion';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Mq::worker($exchange,$queue,function ($message){
            switch ($message->res_type) {
                case 'sentence':
                    $sentence = Sentence::where('uid',$message->res_id)->first();
                    if(!$sentence){
                        return 0;
                    }
                    /**生成消息内容 */
                    $msgTitle = $message->editor->nickName;
                    if($message->parent){
                        $parentTitle = Discussion::where('id',$message->parent)->value('title');
                        $msgTitle .= '回复了 '.$parentTitle;
                    }else{
                        $msgTitle .= '创建了讨论';
                    }
                    $msgContent = '';
                    if($message->title){
                        $msgContent = $message->title.'\n\n';
                    }
                    if($message->content){
                        $msgContent .= $message->content;
                    }

                    $webhooks = WebHook::where('res_id',$sentence->channel_uid)
                                    ->where('status','active')
                                    ->get();
                    foreach ($webhooks as $key => $hook) {
                        $event = json_decode($hook->event);
                        if(!in_array('discussion',$event)){
                            continue;
                        }
                        $command = '';
                        switch ($hook->receiver) {
                            case 'dingtalk':
                                $command = 'webhook:dingtalk';
                                break;
                            case 'wechat':
                                $command = 'webhook:wechat';
                                break;
                            default:
                                # code...
                                break;
                        }
                        $ok = $this->call($command,['url'=>$hook->url,
                                                    'title'=>$msgTitle,
                                                    'message'=>$msgContent,
                                                    ]);
                        $this->info("{$command}  ok={$ok}");
                        if($ok===0){
                            WebHook::where('id',$hook->id)->increment('success');
                        }else{
                            WebHook::where('id',$hook->id)->increment('fail');
                        }
                    }
                    break;
                default:
                    # code...
                    break;
            }
        });

        return 0;
    }
}
