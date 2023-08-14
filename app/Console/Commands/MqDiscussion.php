<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sentence;
use App\Models\WebHook;
use App\Models\Discussion;
use App\Http\Api\Mq;
use App\Tools\WebHook as WebHookSend;

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
                    $msgTitle = '**'. $message->editor->nickName.'**';
                    $link = "https://staging.wikipali.org/pcd/discussion/topic/";
                    if($message->parent){
                        $parentTitle = Discussion::where('id',$message->parent)->value('title');
                        $link .= $message->parent;
                        $msgTitle .= "回复了 [{$parentTitle}]({$link})";
                    }else{
                        $link .= $message->id;
                        $msgTitle .= "创建了讨论[$message->title]({$link})";
                    }
                    $msgContent = $msgTitle;

                    if($message->content){
                        $msgContent .= "\n>".$message->content;
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
                        $whSend = new WebHookSend;

                        switch ($hook->receiver) {
                            case 'dingtalk':
                                $ok = $whSend->dingtalk($hook->url,null,$msgContent);
                                break;
                            case 'wechat':
                                $ok = $whSend->wechat($hook->url,null,$msgContent);
                                break;
                            default:
                                $ok=2;
                                break;
                        }
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
