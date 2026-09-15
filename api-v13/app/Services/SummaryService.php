<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SummaryService
{
    protected string $modelId;

    protected string $apiUrl = '';

    protected string $apiModel = 'deepseek-v3';

    protected int $maxRetries = 3;

    protected int $chunkSize = 20000; // 每段字符数，可根据模型上下文调整

    private string $system_prompt = '你是一个摘要写作助手.请根据用户的输入文本生成中文的摘要,直接输出摘要，无需解释说明。';

    /**
     * 创建服务实例，初始化 OpenAI API Key
     *
     * @return void
     */
    public function __construct(AIModelService $aiModels)
    {
        $models = $aiModels->getSysModels('summarize');
        if (isset($models[0])) {
            $this->modelId = $models[0]['uid'];
        }
        $this->apiUrl = config('mint.ai.proxy').'/api/openai';
    }

    /**
     * 生成输入文本的摘要，并支持缓存与强制刷新。
     *
     * 此方法会根据文本长度自动拆分为多个片段，
     * 对每个片段调用模型生成部分摘要，
     * 并最终将所有部分摘要再次合并生成整体摘要。
     *
     * 同时支持缓存机制：
     * - 缓存键使用文本内容的 md5 计算。
     * - 默认缓存有效期为 1 天。
     * - 可通过 forceRefresh 参数强制重新生成摘要。
     *
     * @param  string  $text  输入的 Markdown 文本
     * @param  int  $maxTokens  每次请求允许的最大 tokens 数
     * @param  bool  $forceRefresh  是否忽略缓存并强制刷新摘要
     * @return string 最终生成的摘要文本
     */
    public function summarize(string $text, int $maxTokens = 500, bool $forceRefresh = false): string
    {
        // 1️⃣ 计算缓存 key
        $cacheKey = 'summary_'.md5($text);

        // 2️⃣ 检查缓存命中
        if (! $forceRefresh && Cache::has($cacheKey)) {
            Log::debug('SummaryService cache hit', ['key' => $cacheKey]);

            return Cache::get($cacheKey);
        }

        Log::debug('SummaryService generating new summary', [
            'key' => $cacheKey,
            'forceRefresh' => $forceRefresh,
        ]);

        // 3️⃣ 执行摘要逻辑
        $chunks = $this->splitText($text, $this->chunkSize);
        $partialSummaries = [];

        foreach ($chunks as $chunk) {
            $summary = $this->callOpenAI($chunk, $maxTokens);
            if ($summary !== '') {
                $partialSummaries[] = $summary;
            }
        }

        if (count($partialSummaries) === 0) {
            Log::warning('SummaryService no partial summaries', ['key' => $cacheKey]);

            return '';
        }

        $finalSummary = '';
        if (count($partialSummaries) === 1) {
            $finalSummary = $partialSummaries[0];
        } else {
            $combinedText = implode("\n\n", $partialSummaries);
            $finalSummary = $this->callOpenAI($combinedText, $maxTokens);
        }

        // 4️⃣ 写入缓存（默认缓存 1 周）
        Cache::put($cacheKey, $finalSummary, now()->addWeek());

        Log::debug('SummaryService cached new summary', [
            'key' => $cacheKey,
            'summary' => mb_substr($finalSummary, 0, 10, 'UTF-8'),
        ]);

        return $finalSummary;
    }

    /**
     * 按段落拆分文本
     *
     * 将 Markdown 文本按空行识别为段落，
     * 避免在段落中间截断。
     * 如果段落超过设定 chunkSize，则按字符截断。
     *
     * @param  string  $text  输入的 Markdown 文本
     * @param  int  $chunkSize  每个块的最大字符数
     * @return array 分割后的文本块数组
     */
    protected function splitText(string $text, int $chunkSize): array
    {
        $paragraphs = preg_split("/\r?\n\r?\n/", $text); // 按空行拆段落
        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if ($para === '') {
                continue;
            }

            // 如果单段落超长，按 chunkSize 截断
            if (mb_strlen($para) > $chunkSize) {
                $subStart = 0;
                while ($subStart < mb_strlen($para)) {
                    $subChunk = mb_substr($para, $subStart, $chunkSize);
                    $chunks[] = $subChunk;
                    $subStart += $chunkSize;
                }

                continue;
            }

            // 如果加上当前段落超过 chunkSize，则先保存当前 chunk
            if (mb_strlen($currentChunk) + mb_strlen($para) + 2 > $chunkSize) { // +2 保留空行
                $chunks[] = $currentChunk;
                $currentChunk = $para;
            } else {
                // 否则累加到当前 chunk
                $currentChunk .= ($currentChunk === '' ? '' : "\n\n").$para;
            }
        }

        if ($currentChunk !== '') {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * 调用 OpenAI GPT 模型生成摘要
     *
     * 带有重试机制和指数退避。
     * 在 429 或 500+ 错误时重试，最大重试次数为 maxRetries。
     * 其他错误直接返回空字符串。
     *
     * @param  string  $text  输入文本
     * @param  int  $maxTokens  每次请求允许的最大 tokens 数
     * @return string 模型返回的摘要文本
     */
    protected function callOpenAI(string $text, int $maxTokens = 200): string
    {
        $attempt = 0;
        $delay = 1;

        $payload = [
            'model' => $this->modelId,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->system_prompt,
                ],
                [
                    'role' => 'user',
                    'content' => $text,
                ],
            ],
            'max_tokens' => $maxTokens,
        ];
        while ($attempt < $this->maxRetries) {
            try {
                $response = Http::timeout(100)
                    ->withHeaders([
                        'Authorization' => 'Bearer ',
                        'Content-Type' => 'application/json',
                    ])->post($this->apiUrl, [
                        'model_id' => $this->modelId,
                        'payload' => $payload,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    return $data['choices'][0]['message']['content'] ?? '';
                }

                if (in_array($response->status(), [429, 500, 502, 503, 504])) {
                    throw new \Exception('Temporary server error: '.$response->status());
                }

                return '';
            } catch (\Exception $e) {
                $attempt++;
                if ($attempt >= $this->maxRetries) {
                    return '';
                }

                sleep($delay);
                $delay *= 10;
            }
        }

        return '';
    }
}
