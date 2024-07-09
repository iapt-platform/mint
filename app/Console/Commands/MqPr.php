<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Http\Api\Mq;
use App\Models\Sentence;
use App\Models\WebHook;
use App\Models\PaliSentence;
use App\Tools\WebHook as WebHookSend;
use App\Http\Api\MdRender;
use App\Http\Api\PaliTextApi;
use App\Http\Controllers\NotificationController;

class MqPr extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan mq:pr
     * @var string
     */
    protected $signature = 'mq:pr';

    protected $ver = '2024-1-2';
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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $exchange = 'router';
        $queue = 'suggestion';
        $this->info(" [*] Waiting for {$queue}. Ver. ".$this->ver);
        Log::debug("mq:pr start. ver=".$this->ver);
        Mq::worker($exchange,$queue,function ($message){
            /**生成消息内容 */

            $msgTitle = '修改建议';
            $prData = $message->data;
            $sent_num = "{$prData->book}-{$prData->paragraph}-{$prData->word_start}-{$prData->word_end}";
            $this->info('ver='.$this->ver.' request'.$sent_num);

            $username = $prData->editor->nickName;
            $palitext = PaliSentence::where('book',$prData->book)
                                ->where('paragraph',$prData->paragraph)
                                ->where('word_begin',$prData->word_start)
                                ->where('word_end',$prData->word_end)
                                ->value('text');
            $orgText = Sentence::where('book_id',$prData->book)
                                ->where('paragraph',$prData->paragraph)
                                ->where('word_start',$prData->word_start)
                                ->where('word_end',$prData->word_end)
                                ->where('channel_uid',$prData->channel->id)
                                ->first();
            $prtext = mb_substr($prData->content,0,140,"UTF-8");

            $link = config('app.url')."/pcd/article/para/{$prData->book}-{$prData->paragraph}";
            $link .= "?book={$prData->book}&par={$prData->paragraph}&channel={$prData->channel->id}";

            $msgContent = "{$username} 就文句`{$palitext}`提出了修改建议：\n";
            $msgContent .= ">内容摘要：<font color=\"comment\">{$prtext}</font>，\n";
            $msgContent .= ">句子编号：<font color=\"info\">{$sent_num}</font>\n";
            $msgContent .= "欢迎大家[点击链接]({$link})查看并讨论。";


            $result=0;
            //发送站内信
            try{
                $sendTo = array();
                if($prData->editor->id !== $prData->channel->studio_id){
                    $sendTo[] = $prData->channel->studio_id;
                }
                if($orgText){
                    //原文作者
                    if(!in_array($orgText->editor_uid,$sendTo) &&
                        $orgText->editor_uid !== $prData->editor->id){
                        $sendTo[] = $orgText->editor_uid;
                    }
                    //原文采纳者
                    if(!empty($orgText->acceptor_uid) &&
                       !in_array($orgText->acceptor_uid,$sendTo) &&
                       $orgText->acceptor_uid !== $prData->editor->id){
                        $sendTo[] = $orgText->acceptor_uid;
                    }
                }
                if(count($sendTo) > 0){
                    $sendCount = NotificationController::insert($prData->editor->id,
                                                    $sendTo,
                                                    'suggestion',
                                                    $prData->uid,
                                                    $prData->channel->id);
                }

                $this->info("send notification success to [".count($sendTo).'] users');
            }catch(\Exception $e){
                $this->error('send notification failed');
                Log::error('send notification failed',['exception'=>$e]);
            }

            //发送webhook

            $webhooks = WebHook::where('res_id',$prData->channel->id)
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
                $result+=$ok;
                if($ok === 0){
                    Log::debug('mq:pr: send success {url}',['url'=>$hook->url]);
                    WebHook::where('id',$hook->id)->increment('success');
                }else{
                    Log::error('mq:pr: send fail {url}',['url'=>$hook->url]);
                    WebHook::where('id',$hook->id)->increment('fail');
                }
            }
            return $result;
        });
        return 0;
    }
}
