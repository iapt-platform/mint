<?php

namespace App\Http\Api;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Illuminate\Support\Facades\Log;

class Mq
{

    private static function connection()
    {
        $host = config("queue.connections.rabbitmq.host");
        $port = config("queue.connections.rabbitmq.port");
        $user = config("queue.connections.rabbitmq.user");
        $password = config("queue.connections.rabbitmq.password");
        $vhost = config("queue.connections.rabbitmq.password");
        if (empty($host) || empty($port) || empty($user) || empty($password) || empty($vhost)) {
            Log::error('rabbitmq set error');
            return;
        }
        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        return $connection;
    }

    public static function publish(string $channelName, $message)
    {
        //一对一
        try {
            Log::debug('mq publish {channel} {message}', ['channel' => $channelName, 'message' => $message]);
            $host = config("queue.connections.rabbitmq.host");
            $port = config("queue.connections.rabbitmq.port");
            $user = config("queue.connections.rabbitmq.user");
            $password = config("queue.connections.rabbitmq.password");
            $vhost = config("queue.connections.rabbitmq.virtual_host");
            if (empty($host) || empty($port) || empty($user) || empty($password) || empty($vhost)) {
                Log::error('rabbitmq set error');
                return;
            }
            $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
            $channel = $connection->channel();
            $channel->queue_declare($channelName, false, true, false, false);

            $msg = new AMQPMessage(json_encode($message, JSON_UNESCAPED_UNICODE));
            $channel->basic_publish($msg, '', $channelName);

            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            Log::error($e);
            return;
        }
    }

    /**
     * @param string $exchange
     * @param string $queue
     * @param callable|null $callback
     */
    public static function worker($exchange, $queue, $callback = null)
    {

        $consumerTag = 'consumer';


        $host = config("queue.connections.rabbitmq.host");
        $port = config("queue.connections.rabbitmq.port");
        $user = config("queue.connections.rabbitmq.user");
        $password = config("queue.connections.rabbitmq.password");
        $vhost = config("queue.connections.rabbitmq.virtual_host");
        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);

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
        $process_message = function ($message) use ($callback, $connection, $exchange, $queue) {
            if ($callback !== null) {
                try {
                    $result = $callback(json_decode($message->body));
                    if (\App\Tools\Tools::isStop()) {
                        Log::debug('mq worker: .stop file exist. cancel the consumer.');
                        $message->getChannel()->basic_cancel($message->getConsumerTag());
                    }
                    if ($result !== 0) {
                        throw new \Exception('task error');
                    }
                } catch (\Exception $e) {
                    // push to issues
                    Log::error('mq worker exception', ['exception'=>$e] );
                    $channelName = 'issues';
                    $channelIssues = $connection->channel();
                    $channelIssues->queue_declare($channelName, false, true, false, false);

                    $msg = new AMQPMessage(json_encode([
                        'exchange' => $exchange,
                        'channel' => $queue,
                        'message' => json_decode($message->body),
                        'result' => 0,
                        'error' => $e,
                    ], JSON_UNESCAPED_UNICODE));
                    $channelIssues->basic_publish($msg, '', $channelName);
                    $channelIssues->close();
                }
            }
            $message->ack();


            // Send a message with the string "quit" to cancel the consumer.
            /*
            if ($message->body === 'quit') {
                $message->getChannel()->basic_cancel($message->getConsumerTag());
            }
            */
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
        $shutdown = function ($channel, $connection) {
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
    }
}
