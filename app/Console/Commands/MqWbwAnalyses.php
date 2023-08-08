<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MqWbwAnalyses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:wbw.analyses';

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

		$channel->queue_declare('wbw-analyses', false, true, false, false);

		$this->info(" [*] Waiting for wbw-analyses. To exit press CTRL+C");

		$callback = function ($msg) {
            $message = json_decode($msg->body);
            $ok = $this->call('upgrade:wbw.analyses',['id'=>implode(',',$message)]);
            $this->info("Received count=".count($message).' ok='.$ok);
		};

		$channel->basic_consume('wbw-analyses', '', false, true, false, false, $callback);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }
}
