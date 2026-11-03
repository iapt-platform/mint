<?php

namespace App\Services;

use App\Tools\Tools;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

/**
 * RabbitMQ 的发布与消费。
 *
 * 消费策略：阻塞等待消息，每次只取一条；任务处理结束无论成功失败一律 ack，
 * 不重试、不进死信队列。失败只记日志。
 */
class RabbitMQService
{
    private AMQPStreamConnection $connection;

    private AMQPChannel $channel;

    /**
     * @var array{host: string, port: int|string, user: string, password: string, virtual_host: string}
     */
    private array $config;

    /**
     * @var array<string, array{max_length?: int}>
     */
    private array $queues;

    public function __construct()
    {
        $this->config = config('queue.connections.rabbitmq');
        $this->queues = config('mint.rabbitmq.queues', []);
        $this->connect();
    }

    private function connect(): void
    {
        $this->connection = new AMQPStreamConnection(
            $this->config['host'],
            $this->config['port'],
            $this->config['user'],
            $this->config['password'],
            $this->config['virtual_host']
        );

        $this->channel = $this->connection->channel();

        // QoS：每次只投递一条未确认消息
        $this->channel->basic_qos(null, 1, null);
    }

    public function isConnected(): bool
    {
        return $this->connection->isConnected();
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    /**
     * 声明配置中的全部队列。
     *
     * @return list<string> 队列名
     */
    public function createQueue(): array
    {
        foreach (array_keys($this->queues) as $name) {
            $this->setupQueue($name);
        }

        return array_keys($this->queues);
    }

    /**
     * 声明一个持久化队列（不存在则创建）。
     */
    public function setupQueue(string $queueName): void
    {
        $queueConfig = $this->queues[$queueName] ?? [];

        $args = [];
        if (! empty($queueConfig['max_length'])) {
            $args['x-max-length'] = (int) $queueConfig['max_length'];
        }

        $this->channel->queue_declare(
            $queueName,
            false,  // passive
            true,   // durable
            false,  // exclusive
            false,  // auto_delete
            false,  // nowait
            new AMQPTable($args)
        );
    }

    public function publishMessage(string $queueName, array $data): string
    {
        try {
            $this->setupQueue($queueName);
            $msgId = (string) Str::uuid();
            $message = new AMQPMessage(
                json_encode($data, JSON_UNESCAPED_UNICODE),
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'timestamp' => time(),
                    'message_id' => $msgId,
                    'content_type' => 'application/json; charset=utf-8',
                ]
            );

            $this->channel->basic_publish($message, '', $queueName);

            Log::info("Message published to queue: {$queueName} msg id={$msgId}");

            return $msgId;
        } catch (\Exception $e) {
            Log::error("Failed to publish message to queue: {$queueName}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 阻塞消费队列。
     *
     * 每条消息处理结束（成功、抛异常、JSON 解析失败）都 ack，不重试。
     * 处理完 $loopLimit 条或检测到 .stop 文件后退出；$loopLimit 为 0 表示不限。
     *
     * @param  callable(mixed $data, AMQPMessage $message): void  $callback
     * @param  int|null  $loopLimit  默认取 mint.mq.consumer_loop_limit
     */
    public function consume(string $queueName, callable $callback, ?int $loopLimit = null): void
    {
        $this->setupQueue($queueName);
        $loopLimit ??= (int) config('mint.mq.consumer_loop_limit', 0);
        $processed = 0;

        $consumerCallback = function (AMQPMessage $msg) use ($callback, $queueName, $loopLimit, &$processed): void {
            $messageId = $msg->has('message_id') ? $msg->get('message_id') : null;
            $context = ['queue' => $queueName, 'message_id' => $messageId];

            try {
                $data = json_decode($msg->getBody(), true, flags: JSON_THROW_ON_ERROR);
                Log::info('mq message start', $context);
                $callback($data, $msg);
                Log::info('mq message done', $context);
            } catch (\Throwable $e) {
                Log::error('mq message failed', $context + [
                    'error' => $e->getMessage(),
                    'exception' => $e::class,
                ]);
            } finally {
                $msg->ack();
            }

            $processed++;

            if (($loopLimit > 0 && $processed >= $loopLimit) || Tools::isStop()) {
                Log::info('mq consumer exit', $context + ['processed' => $processed]);
                $msg->getChannel()->basic_cancel($msg->getConsumerTag());
            }
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume(
            $queueName,
            '',     // consumer_tag
            false,  // no_local
            false,  // no_ack：手动 ack
            false,  // exclusive
            false,  // nowait
            $consumerCallback
        );

        Log::info("mq consumer start queue={$queueName}", ['loop_limit' => $loopLimit]);

        // 阻塞等待，直到回调里 basic_cancel
        $this->channel->consume();

        Log::info("mq consumer stopped queue={$queueName}", ['processed' => $processed]);
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }
}
