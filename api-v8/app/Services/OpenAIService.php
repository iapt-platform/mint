<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;

class OpenAIService
{
    protected int $retries = 3;
    protected int $delayMs = 2000;
    protected string $model = 'gpt-4-1106-preview';
    protected string $apiUrl = 'https://api.openai.com/v1/chat/completions';
    protected string $apiKey;
    protected string $systemPrompt = '你是一个有帮助的助手。';
    protected float $temperature = 0.7;
    protected bool $stream = false;
    protected int $timeout = 600;
    protected int $maxTokens = 0;

    public static function withRetry(int $retries = 3, int $delayMs = 2000): static
    {
        return (new static())->setRetry($retries, $delayMs);
    }

    public function setRetry(int $retries, int $delayMs): static
    {
        $this->retries = $retries;
        $this->delayMs = $delayMs;
        return $this;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function setApiUrl(string $url): static
    {
        $this->apiUrl = $url;
        return $this;
    }

    public function setApiKey(string $key): static
    {
        $this->apiKey = $key;
        return $this;
    }

    public function setSystemPrompt(string $prompt): static
    {
        $this->systemPrompt = $prompt;
        return $this;
    }

    public function setTemperature(float $temperature): static
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function setStream(bool $stream): static
    {
        $this->stream = $stream;

        // 流式时需要无限超时
        if ($stream) {
            $this->timeout = 0;
        }

        return $this;
    }
    public function setMaxToken(int $maxTokens): static
    {
        $this->maxTokens = $maxTokens;
        return $this;
    }
    /**
     * 发送 GPT 请求（支持流式与非流式）
     */
    public function send(string $question): string|array
    {
        for ($attempt = 1; $attempt <= $this->retries; $attempt++) {

            try {
                if ($this->stream === false) {
                    // ⬇ 非流式原逻辑
                    return $this->sendNormal($question);
                }

                // ⬇ 流式逻辑
                return $this->sendStreaming($question);
            } catch (ConnectionException $e) {
                Log::warning("第 {$attempt} 次连接超时：{$e->getMessage()}，准备重试...");
                usleep($this->delayMs * 1000 * pow(2, $attempt));
                continue;
            } catch (\Exception $e) {
                Log::error("GPT 请求异常：" . $e->getMessage());
                return [
                    'error' => $e->getMessage(),
                    'status' => 500
                ];
            }
        }

        return [
            'error' => '请求多次失败或超时，请稍后再试。',
            'status' => 504
        ];
    }

    /**
     * 普通非流式请求
     */
    protected function sendNormal(string $question)
    {
        $data = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $question],
            ],
            'temperature' => $this->temperature,
            'stream' => false,
        ];
        if ($this->maxTokens > 0) {
            $data['max_tokens'] = $this->maxTokens;
        }
        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->post($this->apiUrl, $data);

        $status = $response->status();
        $body = $response->json();

        if ($status === 429) {
            $retryAfter = $response->header('Retry-After') ?? 20;
            Log::warning("请求被限流（429），等待 {$retryAfter} 秒后重试...");
            sleep((int)$retryAfter);
            return $this->sendNormal($question);
        }

        if ($response->successful()) {
            return $body;
        }
        Log::error('llm request error', [
            'error' => $body['error']['message'] ?? '请求失败',
            'status' => $status
        ]);
        return [
            'error' => $body['error']['message'] ?? '请求失败',
            'status' => $status
        ];
    }

    /**
     * =====================================================
     *  ⭐ 原生 CURL SSE + 完整拼接
     *  ⭐ 不输出浏览器
     *  ⭐ 无 IDE 报错（使用 \CurlHandle）
     * =====================================================
     */
    protected function sendStreaming(string $question): string|array
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user', 'content' => $question],
            ],
            'temperature' => $this->temperature,
            'stream' => true,
        ];

        /** @var \CurlHandle $ch */
        $ch = curl_init($this->apiUrl);

        $fullContent = '';

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json",
                "Accept: text/event-stream",
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 0,      // SSE 必须无限
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,

            CURLOPT_WRITEFUNCTION =>
            function (\CurlHandle $curl, string $data) use (&$fullContent): int {

                $lines = explode("\n", $data);

                foreach ($lines as $line) {
                    $line = trim($line);

                    if (!str_starts_with($line, 'data: '))
                        continue;

                    $json = substr($line, 6);

                    if ($json === '[DONE]')
                        continue;

                    $obj = json_decode($json, true);
                    if (!is_array($obj)) continue;

                    $delta = $obj['choices'][0]['delta']['content'] ?? '';
                    if ($delta !== '') {
                        $fullContent .= $delta;
                    }
                }

                return strlen($data);
            },
        ]);

        curl_exec($ch);

        if ($error = curl_error($ch)) {
            curl_close($ch);
            return [
                'error' => $error,
                'status' => 500
            ];
        }

        curl_close($ch);

        return $fullContent;
    }
}
