<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class TestMqWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mqworker';

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

		$channel->queue_declare('hello', false, true, false, false);

		echo " [*] Waiting for messages. To exit press CTRL+C\n";

		$callback = function ($msg) {
			echo ' [x] Received ', $msg->body, "\n";
		};

		$channel->basic_consume('hello', '', false, true, false, false, $callback);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }
}
