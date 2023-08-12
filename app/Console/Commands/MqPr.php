<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Models\Sentence;
use App\Models\WebHook;

class MqPr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:pr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'push pr message to mq';

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

		$channel->queue_declare('suggestion', false, true, false, false);

		$this->info(" [*] Waiting for suggestion. To exit press CTRL+C");

		$callback = function ($msg) {
            $message = json_decode($msg->body);

            /**生成消息内容 */
            $msgTitle = '';
            $username = $message->editor->nickName;
            $palitext = PaliSentence::where('book',$message->book)
                                ->where('paragraph',$message->paragraph)
                                ->where('word_begin',$message->word_start)
                                ->where('word_end',$message->word_end)
                                ->value('text');
            $prtext = mb_substr($message->content,0,140,"UTF-8");
            $sent_num = "{$message->book}-{$message->paragraph}-{$message->word_start}-{$message->word_end}";
            $link = "https://next.wikipali.org/pcd/article/para/{$message->book}-{$message->paragraph}";
            $link .= "?book={$message->book}&par={$message->paragraph}&channel={$message->channel_uid}";

            $msgContent = "{$username} 就文句`{$palitext}`提出了修改建议：
            >内容摘要：<font color=\"comment\">{$prtext}</font>，\n
            >句子编号：<font color=\"info\">{$sent_num}</font>\n
            欢迎大家[点击链接]({$link})查看并讨论。";

            $webhooks = WebHook::where('res_id',$message->channel_uid)
                            ->where('status','active')
                            ->get();
            foreach ($webhooks as $key => $hook) {
                $event = json_decode($hook->event);
                if(!in_array('pr',$event)){
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


		};

		$channel->basic_consume('suggestion', '', false, true, false, false, $callback);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }
}
