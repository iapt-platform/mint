<?php

namespace App\Http\Api;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


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

    public static function publish(string $queue, $message)
    {
        //一对一
        try {
            Log::debug('mq publish', ['queue' => $queue, 'message' => $message]);
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
            $channel->queue_declare($queue, false, true, false, false);

            $msgId = Str::uuid();
            Log::info("mq push message queue={$queue} id={$msgId}");
            $msg = new AMQPMessage(
                json_encode($message, JSON_UNESCAPED_UNICODE),
                [
                    "message_id" => $msgId,
                    "content_type" => 'application/json; charset=utf-8'
                ]
            );
            $channel->basic_publish($msg, '', $queue);

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
        $process_message = function (AMQPMessage $message) use ($callback, $queue) {
            Log::debug('received message', [
                'message_id' => $message->get('message_id'),
                'content_type' => $message->get('content_type')
            ]);
            if ($callback !== null) {
                try {
                    $result = $callback(json_decode($message->getBody()), $message->get('message_id'));
                    Log::debug(
                        'mq done',
                        [
                            'message_id' => $message->get('message_id')
                        ]
                    );
                    if ($result !== 0) {
                        throw new \Exception('task error');
                    }
                } catch (\Exception $e) {
                    Log::error("mq worker {$queue} exception", [
                        'queue' => $queue,
                        'message_id' => $message->get('message_id'),
                        'exception' => $e
                    ]);
                }

                if (\App\Tools\Tools::isStop()) {
                    Log::info('mq worker: .stop file exist. cancel the consumer.');
                    $message->getChannel()->basic_cancel($message->getConsumerTag());
                }
            }


            //exit
            foreach (config('mint.mq.loop_limit') as $key => $value) {
                if ($queue === $key) {
                    if ($value > 0) {
                        if (isset($GLOBALS[$key])) {
                            $GLOBALS[$key]++;
                        } else {
                            $GLOBALS[$key] = 1;
                        }
                        if ($GLOBALS[$key] >= $value) {
                            Log::info("mq exit queue={$queue} loop=" . $GLOBALS[$key]);
                            $message->getChannel()->basic_cancel($message->getConsumerTag());
                        }
                    }
                }
            }
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
        $channel->basic_consume($queue, $consumerTag, false, true, false, false, $process_message);

        /**
         * @param \PhpAmqpLib\Channel\AMQPChannel $channel
         * @param \PhpAmqpLib\Connection\AbstractConnection $connection
         */
        $shutdown = function ($channel, $connection) {
            $channel->close();
            $connection->close();
        };

        register_shutdown_function($shutdown, $channel, $connection);

        $timeout = 15;
        // Loop as long as the channel has callbacks registered
        while ($channel->is_consuming()) {
            try {
                $channel->wait(null, false, $timeout);
            } catch (AMQPTimeoutException $e) {
                // ignore it
            }
        }
    }
}
