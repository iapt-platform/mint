<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Exceptions\TaskFailException;

class RabbitMQService
{
    private $connection;
    private $channel;
    private array $config;
    private array $queues;

    public function __construct()
    {
        $this->config = config('queue.connections.rabbitmq');
        $this->queues = config('mint.rabbitmq.queues');
        $this->connect();
    }

    private function connect()
    {
        $conn = $this->config;
        $this->connection = new AMQPStreamConnection(
            $conn['host'],
            $conn['port'],
            $conn['user'],
            $conn['password'],
            $conn['virtual_host']
        );

        $this->channel = $this->connection->channel();

        // 设置 QoS - 每次只处理一条消息
        $this->channel->basic_qos(null, 1, null);
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    public function createQueue(): array
    {
        foreach ($this->queues as $name => $queue) {

            $args = [];

            // TTL
            if (!empty($queue['ttl'])) {
                $args['x-message-ttl'] = (int) $queue['ttl'];
            }

            // max length
            if (!empty($queue['max_length'])) {
                $args['x-max-length'] = (int) $queue['max_length'];
            }

            // dead letter exchange
            if (!empty($queue['dead_letter_exchange'])) {
                $args['x-dead-letter-exchange'] = $queue['dead_letter_exchange'];
            }

            // dead letter routing key（可选但建议）
            if (!empty($queue['dead_letter_queue'])) {
                $args['x-dead-letter-routing-key'] = $queue['dead_letter_queue'];
            }

            $this->channel->queue_declare(
                $name,                 // queue 名称
                false,                 // passive：检查是否存在（false=不存在则创建）
                true,                  // durable：是否持久化（重启 RabbitMQ 后仍存在）
                false,                 // exclusive：独占模式
                false,                 // auto_delete：是否在无消费者时自动删除
                false,                 // nowait：是否不等待服务器响应（false=阻塞等待确认）
                new AMQPTable($args)   // arguments：附加参数（TTL / DLX / max-length 等）
            );
        }

        return array_keys($this->queues);
    }


    public function setupQueue(string $queueName): void
    {
        $queueConfig = config("mint.rabbitmq.queues.{$queueName}");

        $workerArgs = [];
        if (isset($queueConfig['ttl'])) {
            $workerArgs['x-message-ttl'] =  $queueConfig['ttl'];
        }
        if (isset($queueConfig['max_length'])) {
            $workerArgs['x-max-length'] =  $queueConfig['max_length'];
        }

        // 创建死信交换机
        if (isset($queueConfig['dead_letter_exchange'])) {
            $this->channel->exchange_declare(
                $queueConfig['dead_letter_exchange'],
                'direct',
                false,
                true,
                false
            );

            $dlqName = $queueConfig['dead_letter_queue'];
            $dlqConfig = config("mint.rabbitmq.dead_letter_queues.{$dlqName}", []);
            $dlqArgs = [];
            if (isset($dlqConfig['ttl'])) {
                $dlqArgs['x-message-ttl'] =  $dlqConfig['ttl'];
            }
            if (isset($dlqConfig['max_length'])) {
                $dlqArgs['x-max-length'] =  $dlqConfig['max_length'];
            }
            $dlqArguments = new AMQPTable($dlqArgs);

            // 创建死信队列
            $this->channel->queue_declare(
                $dlqName,
                false,  // passive
                true,   // durable
                false,  // exclusive
                false,  // auto_delete
                false,  // nowait
                $dlqArguments
            );

            // 绑定死信队列到死信交换机
            $this->channel->queue_bind(
                $queueConfig['dead_letter_queue'],
                $queueConfig['dead_letter_exchange']
            );

            // 主队列，配置死信
            $workerArgs['x-dead-letter-exchange'] =  $queueConfig['dead_letter_exchange'];
            $workerArgs['x-dead-letter-routing-key'] =  $queueConfig['dead_letter_queue'];
        }
        $arguments = new AMQPTable($workerArgs);

        $this->channel->queue_declare(
            $queueName,
            false,  // passive
            true,   // durable
            false,  // exclusive
            false,  // auto_delete
            false,  // nowait
            $arguments
        );
        /*
        // 检查队列是否存在
        try {
            $this->channel->queue_declare(
                $queueName,
                true, // passive
                true,   // durable
                false, // exclusive
                false, // auto_delete
                false,  // nowait
                $arguments
            );
            //存在，不用创建
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'NOT_FOUND') !== false) {
                Log::info("Queue $queueName does not exist.");
                // 队列不存在，创建新队列
                $this->channel->queue_declare(
                    $queueName,
                    false,  // passive
                    true,   // durable
                    false,  // exclusive
                    false,  // auto_delete
                    false,  // nowait
                    $arguments
                );
            } else {
                Log::error("Error checking queue $queueName: {$e->getMessage()}");
                throw $e;
            }
        }
            */
    }

    public function publishMessage(string $queueName, array $data): string
    {
        try {
            $this->setupQueue($queueName);
            $msgId = Str::uuid();
            $message = new AMQPMessage(
                json_encode($data, JSON_UNESCAPED_UNICODE),
                [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                    'timestamp' => time(),
                    'message_id' => $msgId,
                    "content_type" => 'application/json; charset=utf-8'
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

    public function consume(string $queueName, callable $callback, int $maxIterations = null): void
    {
        $this->setupQueue($queueName);
        $maxIterations = $maxIterations ?? $this->config['consumer']['max_iterations'];
        $iteration = 0;

        $consumerCallback = function (AMQPMessage $msg) use ($callback, $queueName, &$iteration) {
            try {
                $data = json_decode($msg->getBody(), true);
                $retryCount = $this->getRetryCount($msg);
                $maxRetries = $this->config['queues'][$queueName]['retry_count'];

                Log::info("Processing message from queue: {$queueName}", [
                    'data' => $data,
                    'retry_count' => $retryCount,
                    'delivery_tag' => $msg->getDeliveryTag()
                ]);

                // 执行回调处理消息
                $callback($data, $retryCount);
                // 处理成功，确认消息
                $msg->ack();
                Log::info("Message processed successfully", ['delivery_tag' => $msg->getDeliveryTag()]);
            } catch (TaskFailException $e) {
                //no need requeue
                Log::error("Error processing message", [
                    'error' => $e->getMessage(),
                    'delivery_tag' => $msg->getDeliveryTag()
                ]);
                $msg->nack(false, false);
            } catch (\Exception $e) {
                // 处理失败，检查重试次数
                if ($retryCount < $maxRetries) {
                    // 重新入队，延迟处理
                    $this->requeueWithDelay($msg, $queueName, $retryCount + 1);
                    Log::warning("Message requeued for retry", [
                        'delivery_tag' => $msg->getDeliveryTag(),
                        'retry_count' => $retryCount + 1
                    ]);
                } else {
                    // 超过重试次数，拒绝消息（进入死信队列）
                    $msg->nack(false, false);
                    Log::error("Message rejected after max retries", [
                        'delivery_tag' => $msg->getDeliveryTag(),
                        'retry_count' => $retryCount
                    ]);
                }
            }

            $iteration++;
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($queueName, '', false, false, false, false, $consumerCallback);

        Log::info("Starting consumer for queue: {$queueName}", ['max_iterations' => $maxIterations]);

        while ($this->channel->is_consuming() && $iteration < $maxIterations) {
            $this->channel->wait(null, false, $this->config['consumer']['sleep_between_iterations']);
        }

        Log::info("Consumer stopped", ['iterations_processed' => $iteration]);
    }

    private function getRetryCount(AMQPMessage $msg): int
    {
        $headers = $msg->get_properties();
        return isset($headers['application_headers']['x-retry-count'])
            ? $headers['application_headers']['x-retry-count'] : 0;
    }

    private function requeueWithDelay(AMQPMessage $msg, string $queueName, int $retryCount): void
    {
        $delay = $this->config['queues'][$queueName]['retry_delay'];

        // 创建延迟队列
        $delayQueueName = "{$queueName}_delay_{$retryCount}";
        $arguments = new AMQPTable([
            'x-message-ttl' => $delay,
            'x-dead-letter-exchange' => '',
            'x-dead-letter-routing-key' => $queueName,
        ]);

        $this->channel->queue_declare(
            $delayQueueName,
            false,
            true,
            false,
            false,
            false,
            $arguments
        );

        // 重新发布消息到延迟队列
        $data = json_decode($msg->getBody(), true);
        $newMessage = new AMQPMessage(
            json_encode($data),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'application_headers' => new AMQPTable([
                    'x-retry-count' => $retryCount
                ])
            ]
        );

        $this->channel->basic_publish($newMessage, '', $delayQueueName);
        $msg->ack();
    }

    public function close(): void
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
