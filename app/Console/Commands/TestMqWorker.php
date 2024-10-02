<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

class TestMqWorker extends Command
{
    /**
     * The name and signature of the console command.
     * php artisan test:mq.worker
     * @var string
     */
    protected $signature = 'test:mq.worker';

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
        if(\App\Tools\Tools::isStop()){
            return 0;
        }
        $exchange = 'router';
        $queue = 'hello';
        $consumerTag = 'consumer';
        $connection = new AMQPStreamConnection(config("queue.connections.rabbitmq.host"),
                                            config("queue.connections.rabbitmq.port"),
                                            config("queue.connections.rabbitmq.user"),
                                            config("queue.connections.rabbitmq.password"),
                                            config("queue.connections.rabbitmq.virtual_host"));
        $channel = $connection->channel();

        /*
            The following code is the same both in the consumer and the producer.
            In this way we are sure we always have a queue to consume from and an
                exchange where to publish messages.
        */

        /*
            name: $queue
            passive: false
            durable: true // the queue will survive server restarts
            exclusive: false // the queue can be accessed in other channels
            auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $channel->queue_declare($queue, false, true, false, false);

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */

        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        $channel->queue_bind($queue, $exchange);

        /**
         * @param \PhpAmqpLib\Message\AMQPMessage $message
         */
        $process_message = function ($message)
        {
            echo "\n--------\n";
            echo $message->body;
            echo "\n--------\n";

            $message->ack();

            // Send a message with the string "quit" to cancel the consumer.
            if ($message->body === 'quit') {
                $message->getChannel()->basic_cancel($message->getConsumerTag());
            }
        };

        /*
            queue: Queue from where to get the messages
            consumer_tag: Consumer identifier
            no_local: Don't receive messages published by this consumer.
            no_ack: If set to true, automatic acknowledgement mode will be used by this consumer. See https://www.rabbitmq.com/confirms.html for details.
            exclusive: Request exclusive consumer access, meaning only this consumer can access the queue
            nowait:
            callback: A PHP Callback
        */

        $channel->basic_consume($queue, $consumerTag, false, false, false, false, $process_message);

        /**
         * @param \PhpAmqpLib\Channel\AMQPChannel $channel
         * @param \PhpAmqpLib\Connection\AbstractConnection $connection
         */
        $shutdown = function ($channel, $connection)
        {
            $channel->close();
            $connection->close();
        };

        register_shutdown_function($shutdown, $channel, $connection);

        // Loop as long as the channel has callbacks registered
        while ($channel->is_consuming()) {
            $channel->wait(null, true);
            // do something else
            usleep(300000);
        }
        return 0;
    }
}
