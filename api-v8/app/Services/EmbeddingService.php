<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Services\AIModelService;

class EmbeddingService
{
    protected string $modelId;
    protected string $apiUrl = '';
    protected int $maxRetries = 3;

    /**
     * 创建服务实例，初始化 OpenAI API Key
     *
     * @return void
     */
    public function __construct(AIModelService $aiModels)
    {
        $models = $aiModels->getSysModels('embedding');
        $this->modelId = $models[0]['uid'];
        $this->apiUrl = config('mint.ai.proxy') . '/api/openai';
    }

    public function generate($text)
    {
        return $this->callOpenAI($text);
    }

    /**
     * 调用 OpenAI GPT 模型生成embedding
     *
     * {
  "object": "list",
  "data": [
    {
      "object": "embedding",
      "index": 0,
      "embedding": [
        -0.012345,
        0.021876,
        0.004231,
        -0.037654,
        0.016482,
        -0.001273,
        0.029871,
        -0.015630
        // ... 共1536个浮点数
      ]
    }
  ],
  "model": "text-embedding-3-small",
  "usage": {
    "prompt_tokens": 16,
    "total_tokens": 16
  }
}

     *
     * 带有重试机制和指数退避。
     * 在 429 或 500+ 错误时重试，最大重试次数为 maxRetries。
     * 其他错误直接返回空字符串。
     *
     * @param  string  $text       输入文本
     * @param  int     $maxTokens  每次请求允许的最大 tokens 数
     * @return string              模型返回的摘要文本
     */
    protected function callOpenAI(string $text): string
    {
        $attempt = 0;
        $delay = 1;

        $payload = [
            'model' => $this->modelId,
            'input' => $text,
        ];
        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::timeout(100)
                    ->withHeaders([
                        'Authorization' => 'Bearer ',
                        'Content-Type' => 'application/json',
                    ])->post($this->apiUrl, [
                        'model_id' => $this->modelId,
                        'payload' => $payload
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data['data']['embedding'])) {
                        return $data['data']['embedding'];
                    } else {
                        return false;
                    }
                }

                if (in_array($response->status(), [429, 500, 502, 503, 504])) {
                    throw new \Exception("Temporary server error: " . $response->status());
                }

                return false;
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    return false;
                }

                sleep($delay);
                $delay *= 10;
            }
        }

        return false;
    }
}
