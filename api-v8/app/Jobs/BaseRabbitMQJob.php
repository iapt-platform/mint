<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

abstract class BaseRabbitMQJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $queueName;
    protected $messageData;
    protected $currentRetryCount = 0;
    protected $tries = 0;
    protected $timeout = 0;
    protected $messageId = null;
    protected $stop = false;

    public function __construct(string $queueName, string $messageId, array $messageData, int $retryCount = 0)
    {
        $this->queueName = $queueName;
        $this->messageData = $messageData;
        $this->currentRetryCount = $retryCount;
        $this->messageId = $messageId;

        // 从配置读取重试次数和超时时间
        $queueConfig = config("mint.rabbitmq.queues.{$queueName}");
        $this->tries = $queueConfig['retry_times'] ?? 3;
        $this->timeout = $queueConfig['timeout'] ?? 300;
    }

    public function handle()
    {
        try {
            Log::info("开始处理队列消息", [
                'queue' => $this->queueName,
                'message_id' => $this->messageId ?? 'unknown',
                'retry_count' => $this->currentRetryCount
            ]);

            // 调用子类的具体业务逻辑
            $result = $this->processMessage($this->messageData);

            Log::info("队列消息处理完成", [
                'queue' => $this->queueName,
                'message_id' => $this->messageId ?? 'unknown',
                'result' => $result
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error("队列消息处理失败", [
                'queue' => $this->queueName,
                'message_id' => $this->messageId ?? 'unknown',
                'error' => $e->getMessage(),
                'retry_count' => $this->currentRetryCount,
                'max_retries' => $this->tries
            ]);

            // 如果达到最大重试次数，处理失败逻辑
            if ($this->currentRetryCount >= $this->tries - 1) {
                $this->handleFinalFailure($this->messageData, $e);
            }

            throw $e; // 重新抛出异常以触发重试
        }
    }

    public function failed(\Exception $exception)
    {
        Log::error("队列消息最终失败", [
            'queue' => $this->queueName,
            'message_id' => $this->messageId ?? 'unknown',
            'error' => $exception->getMessage(),
            'retry_count' => $this->currentRetryCount
        ]);

        // 发送到死信队列的逻辑将在 Worker 中处理
    }

    // 子类需要实现的具体业务逻辑
    abstract protected function processMessage(array $messageData);

    // 子类可以重写的失败处理逻辑
    protected function handleFinalFailure(array $messageData, \Exception $exception)
    {
        // 默认实现：记录日志
        Log::error("消息处理最终失败，准备发送到死信队列", [
            'queue' => $this->queueName,
            'message_id' => $this->messageId ?? 'unknown',
            'error' => $exception->getMessage()
        ]);
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getCurrentRetryCount(): int
    {
        return $this->currentRetryCount;
    }
    public function stop()
    {
        $this->stop = true;
    }
}
