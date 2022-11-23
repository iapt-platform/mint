<?php
namespace App\Console\Workers;
class Workers{
    protected $queue = 'hello';
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $connection = new AMQPStreamConnection(MQ_HOST, MQ_PORT, MQ_USERNAME, MQ_PASSWORD);
		$channel = $connection->channel();

		$channel->queue_declare($this->queue, false, true, false, false);

		echo " [*] Waiting for messages. To exit press CTRL+C\n";

		$channel->basic_consume($this->queue, '', false, true, false, false, $this->job);

		while ($channel->is_open()) {
			  $channel->wait();
		  }
        return 0;
    }

    public function job($msg){
        echo ' [x] Received ', $msg->body, "\n";
    }
}
