<?php

namespace App\Http\Api;

use App\Tools\Tools;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Message\AMQPMessage;

class Mq
{
    private static function connection()
    {
        $host = config('queue.connections.rabbitmq.host');
        $port = config('queue.connections.rabbitmq.port');
        $user = config('queue.connections.rabbitmq.user');
        $password = config('queue.connections.rabbitmq.password');
        $vhost = config('queue.connections.rabbitmq.password');
        if (empty($host) || empty($port) || empty($user) || empty($password) || empty($vhost)) {
            Log::error('rabbitmq set error');

            return;
        }
        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);

        return $connection;
    }

    public static function publish(string $queue, $message)
    {
        // 一对一
        try {
            Log::debug('mq publish', ['queue' => $queue, 'message' => $message]);
            $host = config('queue.connections.rabbitmq.host');
            $port = config('queue.connections.rabbitmq.port');
            $user = config('queue.connections.rabbitmq.user');
            $password = config('queue.connections.rabbitmq.password');
            $vhost = config('queue.connections.rabbitmq.virtual_host');
            if (empty($host) || empty($port) || empty($user) || empty($password) || empty($vhost)) {
                Log::error('rabbitmq set error');

                return;
            }
            $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
            $channel = $connection->channel();
            // $channel->queue_declare($queue, false, true, false, false);

            $msgId = Str::uuid();
            Log::info("mq push message queue={$queue} id={$msgId}");
            $msg = new AMQPMessage(
                json_encode($message, JSON_UNESCAPED_UNICODE),
                [
                    'message_id' => $msgId,
                    'content_type' => 'application/json; charset=utf-8',
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
     * 阻塞消费一个已存在的队列。
     *
     * - 队列必须事先建好（`php artisan app:create-queue`），不存在就记日志并抛异常退出，worker 不再自建队列。
     * - 手动 ack、prefetch 1：每条消息处理结束无论成功失败一律 ack，不重试、不进死信队列。
     * - 处理满 `mint.mq.consumer_loop_limit` 条（0 = 不限）或检测到 `.stop` 文件后退出。
     *
     * @param  callable(mixed $data, ?string $messageId): int|null  $callback  返回非 0 视为失败（只记日志）
     *
     * @throws \RuntimeException 队列不存在
     */
    public static function worker(string $queue, ?callable $callback = null): void
    {
        $consumerTag = '';

        $host = config('queue.connections.rabbitmq.host');
        $port = config('queue.connections.rabbitmq.port');
        $user = config('queue.connections.rabbitmq.user');
        $password = config('queue.connections.rabbitmq.password');
        $vhost = config('queue.connections.rabbitmq.virtual_host');
        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);

        $channel = $connection->channel();

        // passive：只检查不创建。不存在时服务端返回 404 并关闭 channel
        try {
            $channel->queue_declare($queue, true);
        } catch (AMQPProtocolChannelException $e) {
            Log::error("mq worker: queue {$queue} not found", ['error' => $e->getMessage()]);
            $connection->close();

            throw new \RuntimeException("mq queue [{$queue}] does not exist, run `php artisan app:create-queue` first", 0, $e);
        }

        $loopLimit = (int) config('mint.mq.consumer_loop_limit', 0);
        $processed = 0;

        $process_message = function (AMQPMessage $message) use ($callback, $queue, $loopLimit, &$processed) {
            $messageId = $message->has('message_id') ? $message->get('message_id') : null;
            Log::debug('received message', [
                'message_id' => $messageId,
                'content_type' => $message->has('content_type') ? $message->get('content_type') : null,
            ]);

            try {
                if ($callback !== null) {
                    $result = $callback(json_decode($message->getBody()), $messageId);
                    Log::debug('mq done', ['message_id' => $messageId]);
                    if ($result !== 0) {
                        throw new \Exception('task error');
                    }
                }
            } catch (\Throwable $e) {
                Log::error("mq worker {$queue} exception", [
                    'queue' => $queue,
                    'message_id' => $messageId,
                    'exception' => $e,
                ]);
            } finally {
                $message->ack();
            }

            $processed++;

            if ($loopLimit > 0 && $processed >= $loopLimit) {
                Log::info("mq exit queue={$queue} loop={$processed}");
                $message->getChannel()->basic_cancel($message->getConsumerTag());
            } elseif (Tools::isStop()) {
                Log::info('mq worker: .stop file exist. cancel the consumer.');
                $message->getChannel()->basic_cancel($message->getConsumerTag());
            }
        };

        $channel->basic_qos(null, 1, false);
        // no_ack = false：手动 ack
        $channel->basic_consume($queue, $consumerTag, false, false, false, false, $process_message);

        try {
            $channel->consume();
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
        }

        $channel->close();
        $connection->close();

        Log::info("mq worker stopped queue={$queue} processed={$processed}");
    }
}
