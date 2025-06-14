<?php

namespace App\Jobs;

use App\Services\AiTranslateService;
use Illuminate\Support\Facades\Log;

class ProcessAITranslateJob extends BaseRabbitMQJob
{
    private $aiService;
    protected function processMessage(array $messageData)
    {
        $startTime = microtime(true);
        try {
            // Laravel会自动注入
            $this->aiService = app(AiTranslateService::class);
            return $this->aiService->processTranslate($this->messageId, $messageData);
        } catch (\Exception $e) {
            // 记录失败指标

            throw $e;
        } finally {
            // 记录处理时间
            $processingTime = microtime(true) - $startTime;
            Log::info('翻译处理耗时', ['time' => $processingTime]);
        }
    }

    protected function handleFinalFailure(array $messageData, \Exception $exception)
    {
        parent::handleFinalFailure($messageData, $exception);

        // 消息处理最终失败，准备发送到死信队列
        $this->aiService->handleFailedTranslate($this->messageId, $messageData, $exception);
    }
    public function stop()
    {
        parent::stop();
        $this->aiService->stop();
    }

    public static function publish(AiTranslateService $ai) {}
}
