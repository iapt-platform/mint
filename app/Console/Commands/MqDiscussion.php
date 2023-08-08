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
		$connection = new AMQPStreamConnection(env("MQ_HOST"), env("MQ_PORT"), env("MQ_USERNAME"), env("MQ_PASSWORD"));
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
                    $webhook = WebHook::where('res_id',$sentence->channel_uid)
                                    ->where('status','active')
                                    ->first();
                    if(!$webhook){
                        return 0;
                    }
                    $event = json_decode($webhook->event);
                    if(!in_array('discussion',$event)){
                        return 0;
                    }
                    switch ($webhook->receiver) {
                        case 'dingtalk':
                            $ok = $this->call('webhook:dingtalk',['url'=>$webhook->url,
                                                                'title'=>'讨论',
                                                                'message'=>'句子：添加新的讨论',
                                                                    ]);
                            $this->info("Received  ok=".$ok);
                            break;
                        default:
                            # code...
                            break;
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
