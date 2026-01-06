<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

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
        $lastException = null;

        for ($attempt = 1; $attempt <= $this->retries; $attempt++) {
            try {
                if ($this->stream === false) {
                    return $this->sendNormal($question);
                }

                return $this->sendStreaming($question);
            } catch (RateLimitException $e) {
                // 429 速率限制，等待后重试
                $retryAfter = $e->getRetryAfter();
                Log::warning("请求被限流（429），等待 {$retryAfter} 秒后重试...（第 {$attempt} 次）");
                sleep($retryAfter);
                $lastException = $e;
                continue;
            } catch (ServerErrorException $e) {
                // 5xx 服务器错误，使用指数退避重试
                Log::warning("服务器错误（{$e->getStatusCode()}）：{$e->getMessage()}，准备重试...（第 {$attempt} 次）");
                if ($attempt < $this->retries) {
                    usleep($this->delayMs * 1000 * pow(2, $attempt - 1));
                }
                $lastException = $e;
                continue;
            } catch (ConnectionException $e) {
                // 网络连接错误，使用指数退避重试
                Log::warning("第 {$attempt} 次连接超时：{$e->getMessage()}，准备重试...");
                if ($attempt < $this->retries) {
                    usleep($this->delayMs * 1000 * pow(2, $attempt - 1));
                }
                $lastException = $e;
                continue;
            } catch (NetworkException $e) {
                // 其他网络错误，使用指数退避重试
                Log::warning("网络错误：{$e->getMessage()}，准备重试...（第 {$attempt} 次）");
                if ($attempt < $this->retries) {
                    usleep($this->delayMs * 1000 * pow(2, $attempt - 1));
                }
                $lastException = $e;
                continue;
            } catch (ClientErrorException $e) {
                // 4xx 客户端错误（除429外）不重试，直接抛出
                Log::error("客户端错误（{$e->getStatusCode()}）：{$e->getMessage()}");
                throw $e;
            } catch (\Exception $e) {
                // 其他未知异常，不重试，直接抛出
                Log::error("GPT 请求异常：" . $e->getMessage());
                throw $e;
            }
        }

        // 所有重试都失败了
        Log::error("请求多次失败，已重试 {$this->retries} 次");
        throw new \RuntimeException(
            '请求多次失败或超时，请稍后再试。原因: ' . ($lastException ? $lastException->getMessage() : '未知'),
            504,
            $lastException
        );
    }

    /**
     * 普通非流式请求
     */
    protected function sendNormal(string $question): array
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

        // 处理 429 速率限制
        if ($status === 429) {
            $retryAfter = (int)($response->header('Retry-After') ?? 20);
            throw new RateLimitException(
                $body['error']['message'] ?? '请求被限流',
                $status,
                $retryAfter
            );
        }

        // 处理 5xx 服务器错误
        if ($status >= 500 && $status < 600) {
            throw new ServerErrorException(
                $body['error']['message'] ?? '服务器错误',
                $status
            );
        }

        // 处理 4xx 客户端错误
        if ($status >= 400 && $status < 500) {
            throw new ClientErrorException(
                $body['error']['message'] ?? '客户端请求错误',
                $status
            );
        }

        // 处理成功响应
        if ($response->successful()) {
            return $body;
        }

        // 其他未知错误
        throw new \RuntimeException(
            $body['error']['message'] ?? '请求失败',
            $status
        );
    }

    /**
     * 流式请求（使用原生 CURL SSE）
     */
    protected function sendStreaming(string $question): string
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

        if ($this->maxTokens > 0) {
            $payload['max_tokens'] = $this->maxTokens;
        }

        $ch = curl_init($this->apiUrl);
        $fullContent = '';
        $httpCode = 0;
        $errorMessage = '';

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->apiKey}",
                "Content-Type: application/json",
                "Accept: text/event-stream",
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_WRITEFUNCTION => function (\CurlHandle $curl, string $data) use (&$fullContent, &$errorMessage): int {
                $lines = explode("\n", $data);

                foreach ($lines as $line) {
                    $line = trim($line);

                    if (!str_starts_with($line, 'data: ')) {
                        continue;
                    }

                    $json = substr($line, 6);

                    if ($json === '[DONE]') {
                        continue;
                    }

                    $obj = json_decode($json, true);
                    if (!is_array($obj)) {
                        continue;
                    }

                    // 检查是否有错误
                    if (isset($obj['error'])) {
                        $errorMessage = $obj['error']['message'] ?? 'Stream error';
                        return 0; // 停止接收
                    }

                    $delta = $obj['choices'][0]['delta']['content'] ?? '';
                    if ($delta !== '') {
                        $fullContent .= $delta;
                    }
                }

                return strlen($data);
            },
        ]);

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($curlError = curl_error($ch)) {
            curl_close($ch);
            throw new NetworkException("CURL 错误: {$curlError}");
        }

        curl_close($ch);

        // 检查流式响应中的错误
        if ($errorMessage) {
            if ($httpCode === 429) {
                throw new RateLimitException($errorMessage, $httpCode);
            } elseif ($httpCode >= 500) {
                throw new ServerErrorException($errorMessage, $httpCode);
            } elseif ($httpCode >= 400) {
                throw new ClientErrorException($errorMessage, $httpCode);
            } else {
                throw new \RuntimeException($errorMessage, $httpCode);
            }
        }

        // 检查 HTTP 状态码
        if ($httpCode === 429) {
            throw new RateLimitException('请求被限流', $httpCode);
        } elseif ($httpCode >= 500) {
            throw new ServerErrorException('服务器错误', $httpCode);
        } elseif ($httpCode >= 400) {
            throw new ClientErrorException('客户端请求错误', $httpCode);
        } elseif ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException("HTTP 错误: {$httpCode}");
        }

        return $fullContent;
    }
}

/**
 * 速率限制异常（429）
 */
class RateLimitException extends \RuntimeException
{
    protected int $retryAfter;

    public function __construct(string $message, int $code = 429, int $retryAfter = 20)
    {
        parent::__construct($message, $code);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }
}

/**
 * 服务器错误异常（5xx）
 */
class ServerErrorException extends \RuntimeException
{
    public function __construct(string $message, int $code = 500)
    {
        parent::__construct($message, $code);
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }
}

/**
 * 客户端错误异常（4xx，除429外）
 */
class ClientErrorException extends \RuntimeException
{
    public function __construct(string $message, int $code = 400)
    {
        parent::__construct($message, $code);
    }

    public function getStatusCode(): int
    {
        return $this->code;
    }
}

/**
 * 网络错误异常
 */
class NetworkException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
