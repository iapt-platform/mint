<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Api\Mq;
use App\Models\Sentence;
use App\Models\WebHook;
use App\Models\PaliSentence;
use App\Tools\WebHook as WebHookSend;
use App\Http\Api\MdRender;

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
        $exchange = 'router';
        $queue = 'suggestion';
        $this->info(" [*] Waiting for {$queue}. To exit press CTRL+C");
        Mq::worker($exchange,$queue,function ($message){
            /**生成消息内容 */
            $msgTitle = '修改建议';
            $username = $message->editor->nickName;
            $palitext = PaliSentence::where('book',$message->book)
                                ->where('paragraph',$message->paragraph)
                                ->where('word_begin',$message->word_start)
                                ->where('word_end',$message->word_end)
                                ->value('text');
            $prtext = mb_substr($message->content,0,140,"UTF-8");
            $sent_num = "{$message->book}-{$message->paragraph}-{$message->word_start}-{$message->word_end}";
            $link = "https://next.wikipali.org/pcd/article/para/{$message->book}-{$message->paragraph}";
            $link .= "?book={$message->book}&par={$message->paragraph}&channel={$message->channel->id}";

            $msgContent = "{$username} 就文句`{$palitext}`提出了修改建议：";
            $msgContent .= ">内容摘要：<font color=\"comment\">{$prtext}</font>，\n";
            $msgContent .= ">句子编号：<font color=\"info\">{$sent_num}</font>\n";
            $msgContent .= "欢迎大家[点击链接]({$link})查看并讨论。";

            $webhooks = WebHook::where('res_id',$message->channel->id)
                            ->where('status','active')
                            ->get();
            foreach ($webhooks as $key => $hook) {
                $event = json_decode($hook->event);
                if(!in_array('pr',$event)){
                    continue;
                }
                $command = '';
                $whSend = new WebHookSend;
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
                $this->info("{$command}  ok={$ok}");
                if($ok===0){
                    WebHook::where('id',$hook->id)->increment('success');
                }else{
                    WebHook::where('id',$hook->id)->increment('fail');
                }
            }
        });

		$callback = function ($msg) {
            $message = json_decode($msg->body);




		};

		$channel->basic_consume('suggestion', '', false, true, false, false, $callback);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }
}
