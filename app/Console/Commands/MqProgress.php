<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class MqProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:progress';

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

		$channel->queue_declare('progress', false, true, false, false);

		$this->info(" [*] Waiting for messages. To exit press CTRL+C");

		$callback = function ($msg) {
            $message = json_decode($msg->body);

            $ok = $this->call('upgrade:progress',['--book'=>$message->book,
                                            '--para'=>$message->para,
                                            '--channel'=>$message->channel,
                                            ]);
            $ok2 = $this->call('upgrade:progress.chapter',['--book'=>$message->book,
                                                '--para'=>$message->para,
                                                '--channel'=>$message->channel,
                                                ]);
            $this->info("Received book=".$message->book.' progress='.$ok.' chapter='.$ok2);
		};

		$channel->basic_consume('progress', '', false, true, false, false, $callback);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }
}
