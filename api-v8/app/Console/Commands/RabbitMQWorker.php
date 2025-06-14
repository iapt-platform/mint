<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Message\AMQPMessage;
use App\Jobs\ProcessAITranslateJob;
use App\Jobs\BaseRabbitMQJob;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Wire\AMQPTable;
use App\Services\RabbitMQService;
use App\Exceptions\SectionTimeoutException;

class RabbitMQWorker extends Command
{
    /**
     * The name and signature of the console command.
     * php -d memory_limit=128M artisan rabbitmq:consume ai_translate
     * @var string
     */
    protected $signature = 'rabbitmq:consume {queue} {--reset-loop-count}';
    protected $description = '消费 RabbitMQ 队列消息';

    private $connection;
    private $channel;
    private $processedCount = 0;
    private $maxLoopCount = 0;
    private $queueName;
    private $queueConfig;
    private $shouldStop = false;
    private $timeout = 15;
    private $job = null;

    public function handle()
    {
        if (\App\Tools\Tools::isStop()) {
            return 0;
        }
        $this->queueName = $this->argument('queue');
        $this->queueConfig = config("mint.rabbitmq.queues.{$this->queueName}");

        if (!$this->queueConfig) {
            $this->error("队列 {$this->queueName} 的配置不存在");
            return 1;
        }

        $this->maxLoopCount = $this->queueConfig['max_loop_count'];

        $this->info("启动 RabbitMQ Worker");
        $this->info("队列: {$this->queueName}");
        $this->info("最大循环次数: {$this->maxLoopCount}");
        $this->info("重试次数: {$this->queueConfig['retry_times']}");
        $consume = app(RabbitMQService::class);
        try {
            $consume->setupQueue($this->queueName);
            $this->channel = $consume->getChannel();
            $this->startConsuming();
        } catch (\Exception $e) {
            $this->error("Worker 启动失败: " . $e->getMessage());
            Log::error("RabbitMQ Worker 启动失败", [
                'queue' => $this->queueName,
                'error' => $e->getMessage()
            ]);
            return 1;
        } finally {
            $this->cleanup();
        }

        return 0;
    }

    private function startConsuming()
    {
        $callback = function (AMQPMessage $msg) {
            $this->processMessage($msg);
        };

        $this->channel->basic_consume(
            $this->queueName,
            '',     // consumer_tag
            false,  // no_local
            false,  // no_ack
            false,  // exclusive
            false,  // nowait
            $callback
        );

        $this->info("开始消费消息... 按 Ctrl+C 退出");

        // 设置信号处理
        if (extension_loaded('pcntl')) {
            pcntl_signal(SIGTERM, [$this, 'handleSignal']);
            pcntl_signal(SIGINT, [$this, 'handleSignal']);
        }

        while ($this->channel->is_consuming() && !$this->shouldStop) {
            try {
                $this->channel->wait(null, false, $this->timeout);
            } catch (AMQPTimeoutException $e) {
                //忽略
            } catch (\Exception $e) {
                $this->error($e->getMessage());
                throw $e;
            }


            if (extension_loaded('pcntl')) {
                pcntl_signal_dispatch();
            }

            // 检查是否达到最大循环次数
            if ($this->processedCount >= $this->maxLoopCount) {
                $this->info("达到最大循环次数 ({$this->maxLoopCount})，Worker 自动退出");
                break;
            }
            if (\App\Tools\Tools::isStop()) {
                //检测到停止标记
                break;
            }
        }
    }

    private function processMessage(AMQPMessage $msg)
    {
        try {

            Log::info('processMessage start', ['message_id' => $msg->get('message_id')]);

            $data = json_decode($msg->getBody());
            $this->info("processMessage start " . $msg->get('message_id') . '[' . count($data) . ']');

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("JSON 解析失败: " . json_last_error_msg());
            }

            // 获取重试次数（从消息头中获取）
            $retryCount = 0;
            if ($msg->has('application_headers')) {
                $headers = $msg->get('application_headers')->getNativeData();
                $retryCount = $headers['retry_count'] ?? 0;
            }

            // 根据队列类型创建对应的 Job
            $this->job = $this->createJob($msg->get('message_id'), $data, $retryCount);

            try {
                // 执行业务逻辑
                $successful = $this->job->handle();
                if ($successful) {
                    // 成功处理，确认消息
                    $msg->ack();
                }

                $this->processedCount++;

                $this->info("消息处理成功 [{$this->processedCount}/{$this->maxLoopCount}]");
            } catch (SectionTimeoutException $e) {
                $msg->nack(true, false);
                Log::warning('attempt to requeue the message message_id:' . $msg->get('message_id'));
            } catch (\Exception $e) {
                $this->handleJobException($msg, $data, $retryCount, $e);
            }
        } catch (\Exception $e) {
            $this->error("消息处理异常: " . $e->getMessage());
            Log::error("RabbitMQ 消息处理异常", [
                'queue' => $this->queueName,
                'error' => $e->getMessage(),
                'message_body' => $msg->getBody()
            ]);

            // 拒绝消息并发送到死信队列
            //$msg->nack(false, false);
            $this->sendToDeadLetterQueue($data, $e);
            $msg->ack(); // 确认原消息以避免重复
            $this->error("已发送到死信队列");
            $this->processedCount++;
        }
    }

    private function createJob(string $messageId, array $data, int $retryCount): BaseRabbitMQJob
    {
        // 根据队列名称创建对应的 Job 实例
        switch ($this->queueName) {
            case 'ai_translate':
                return new ProcessAITranslateJob(
                    $this->queueName,
                    $messageId,
                    $data,
                    $retryCount,
                );
                // 可以添加更多队列类型
            default:
                throw new \Exception("未知的队列类型: {$this->queueName}");
        }
    }

    private function handleJobException(AMQPMessage $msg, array $data, int $retryCount, \Exception $e)
    {
        $maxRetries = $this->queueConfig['retry_times'];

        if ($retryCount < $maxRetries - 1) {
            // 还有重试机会，重新入队
            $this->requeueMessage($msg, $data, $retryCount + 1);
            $this->info("消息重新入队，重试次数: " . ($retryCount + 1) . "/{$maxRetries}");
        } else {
            // 超过重试次数，发送到死信队列
            $this->sendToDeadLetterQueue($data, $e);
            $msg->ack(); // 确认原消息以避免重复
            $this->error("消息超过最大重试次数，已发送到死信队列 ");
            Log::error("消息超过最大重试次数，已发送到死信队列 message_id=" . $msg->get('message_id'));
        }

        $this->processedCount++;
    }

    private function requeueMessage(AMQPMessage $msg, array $data, int $newRetryCount)
    {
        // 添加重试计数到消息头
        // 使用 AMQPTable 包装头部数据
        $headers = new AMQPTable([
            'retry_count' => $newRetryCount,
            'original_queue' => $this->queueName,
            'retry_timestamp' => time()
        ]);

        $newMsg = new AMQPMessage(
            json_encode($data, JSON_UNESCAPED_UNICODE),
            [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                'timestamp' => time(),
                'message_id' => $msg->get('message_id'),
                'application_headers' => $headers,
                "content_type" => 'application/json; charset=utf-8'
            ]
        );

        // 发布到同一队列
        $this->channel->basic_publish($newMsg, '', $this->queueName);

        // 确认原消息
        $msg->ack();
    }

    private function sendToDeadLetterQueue(array $data, \Exception $e)
    {
        $dlqName = $this->queueConfig['dead_letter_queue'];

        $dlqData = [
            'original_message' => $data,
            'failure_reason' => $e->getMessage(),
            'failed_at' => date('Y-m-d H:i:s'),
            'queue' => $this->queueName,
            'max_retries' => $this->queueConfig['retry_times']
        ];

        $dlqMsg = new AMQPMessage(
            json_encode($dlqData),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($dlqMsg, '', $dlqName);

        Log::error("消息发送到死信队列", [
            'original_queue' => $this->queueName,
            'dead_letter_queue' => $dlqName,
            'error' => $e->getMessage()
        ]);
    }

    public function handleSignal($signal)
    {
        $this->info("接收到退出信号，正在优雅关闭...");
        $this->shouldStop = true;
        if ($this->job) {
            $this->job->stop();
        }
        if ($this->channel && $this->channel->is_consuming()) {
            //$this->channel->basic_cancel_on_shutdown(true);
            $this->channel->basic_cancel('');
        }
    }

    private function cleanup()
    {
        try {
            if ($this->channel) {
                $this->channel->close();
            }
            if ($this->connection) {
                $this->connection->close();
            }

            $this->info("连接已关闭，处理了 {$this->processedCount} 条消息");
        } catch (\Exception $e) {
            $this->error("清理资源时出错: " . $e->getMessage());
        }
    }
}
