<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Models\Sentence;
use App\Models\WebHook;

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
		$connection = new AMQPStreamConnection(env("RABBITMQ_HOST"), env("RABBITMQ_PORT"), env("RABBITMQ_USERNAME"), env("RABBITMQ_PASSWORD"));
		$channel = $connection->channel();

		$channel->queue_declare('discussion', false, true, false, false);

		$this->info(" [*] Waiting for wbw-analyses. To exit press CTRL+C");

		$callback = function ($msg) {
            $message = json_decode($msg->body);
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

		};

		$channel->basic_consume('discussion', '', false, true, false, false, $callback);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }
}
