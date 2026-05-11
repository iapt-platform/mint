<?php

namespace App\Services\AIAssistant;

use App\Services\NissayaParser;
use App\Services\OpenAIService;
use App\Services\RomanizeService;
use App\Services\AIModelService;
use App\Services\AuthService;

use Illuminate\Support\Facades\Log;
use App\Http\Resources\AiModelResource;

use App\DTO\LLMTranslation\TranslationResponseDTO;

class TranslateService
{
    protected OpenAIService $openAIService;
    protected NissayaParser $nissayaParser;
    protected RomanizeService $romanizeService;
    protected AIModelService $aiModelService;
    protected AiModelResource $model;
    protected string $modelToken;

    protected bool $stream = false;
    protected array $original; //需要被翻译的原文

    protected string $systemPrompt = '';
    /**
     * 翻译提示词模板
     */
    protected string $translatePrompt = '';

    public function __construct(
        OpenAIService $openAIService,
        AIModelService $aiModelService,
    ) {
        $this->openAIService = $openAIService;
        $this->aiModelService = $aiModelService;
    }

    /**
     * 设置模型配置
     *
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self
    {
        $this->model = $this->aiModelService->getModelById($model);
        $this->modelToken = AuthService::getUserToken($model);
        return $this;
    }
    /**
     * 设置翻译提示词
     *
     * @param string $prompt
     * @return self
     */
    public function setSystemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;
        return $this;
    }
    /**
     * 设置翻译提示词
     *
     * @param string $prompt
     * @return self
     */
    public function setTranslatePrompt(string $prompt): self
    {
        $this->translatePrompt = $prompt;
        return $this;
    }


    /**
     * 翻译缅文版逐词解析
     *
     * @param string $text 格式: 巴利文=缅文
     * @param bool $stream 是否流式输出
     * @return TranslationResponseDTO
     * @throws \Exception
     */
    public function translate(): TranslationResponseDTO
    {
        $startAt = time();

        try {


            Log::info('准备翻译', [
                'systemPrompt' => $this->systemPrompt,
                'translatePrompt' => $this->translatePrompt,
            ]);

            // 3. 调用LLM进行翻译
            $response = $this->openAIService
                ->setApiUrl($this->model['url'])
                ->setModel($this->model['model'])
                ->setApiKey($this->model['key'])
                ->setSystemPrompt($this->systemPrompt)
                ->setTemperature(0.3)
                ->setStream($this->stream)
                ->send($this->translatePrompt);

            $complete = time() - $startAt;
            $content = $response['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                throw new \Exception('LLM返回内容为空');
            }
            Log::info('翻译完成', [
                'content' => $content,
                'duration' => $complete,
                'input_tokens' => $response['usage']['prompt_tokens'] ?? 0,
                'output_tokens' => $response['usage']['completion_tokens'] ?? 0,
            ]);
            // 4. 解析JSONL格式的翻译结果
            $translatedData = $this->jsonlToArray($content);

            Log::info('解析完成', [
                'output_items' => count($translatedData),
            ]);

            return TranslationResponseDTO::fromArray([
                'success' => true,
                'data' => $translatedData,
                'meta' => [
                    'duration' => $complete,
                    'items_count' => count($translatedData),
                    'usage' => $response['usage'] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('NissayaTranslate: 翻译失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return TranslationResponseDTO::fromArray([
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ]);
        }
    }


    /**
     * 将数组转换为JSONL格式
     *
     * @param array $data
     * @return string
     */
    protected function arrayToJsonl(array $data): string
    {
        $lines = [];
        foreach ($data as $item) {
            $lines[] = json_encode($item, JSON_UNESCAPED_UNICODE);
        }
        return implode("\n", $lines);
    }

    /**
     * 将JSONL格式转换为数组
     *
     * @param string $jsonl
     * @return array
     */
    protected function jsonlToArray(string $jsonl): array
    {
        // 清理可能的markdown代码块标记
        $jsonl = preg_replace('/```json\s*|\s*```/', '', $jsonl);
        $jsonl = trim($jsonl);

        $lines = explode("\n", $jsonl);
        $result = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $decoded = json_decode($line, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $result[] = $decoded;
            } else {
                Log::warning('无法解析JSON行', [
                    'line' => $line,
                    'error' => json_last_error_msg(),
                ]);
            }
        }

        return $result;
    }

    /**
     * 批量翻译(将大文本分批处理)
     *
     * @param string $text
     * @param int $batchSize 每批处理的条目数
     * @return array
     */
    public function translateInBatches(string $text, int $batchSize = 50): array
    {
        try {
            $parsedData = $this->nissayaParser->parse($text);
            $batches = array_chunk($parsedData, $batchSize);
            $allResults = [];
            $totalDuration = 0;
            $totalUsage = [
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'total_tokens' => 0,
            ];

            foreach ($batches as $index => $batch) {
                Log::info("NissayaTranslate: 处理批次 " . ($index + 1) . "/" . count($batches));

                $jsonlInput = $this->arrayToJsonl($batch);
                $response = $this->openAIService
                    ->setApiUrl($this->model['url'])
                    ->setModel($this->model['model'])
                    ->setApiKey($this->model['key'])
                    ->setSystemPrompt($this->translatePrompt)
                    ->setTemperature(0.7)
                    ->setStream(false)
                    ->send($jsonlInput);

                $content = $response['choices'][0]['message']['content'] ?? '';
                $translatedBatch = $this->jsonlToArray($content);
                $allResults = array_merge($allResults, $translatedBatch);

                // 累计使用统计
                if (isset($response['usage'])) {
                    $totalUsage['prompt_tokens'] += $response['usage']['prompt_tokens'] ?? 0;
                    $totalUsage['completion_tokens'] += $response['usage']['completion_tokens'] ?? 0;
                    $totalUsage['total_tokens'] += $response['usage']['total_tokens'] ?? 0;
                }
            }

            return [
                'success' => true,
                'data' => $allResults,
                'meta' => [
                    'batches' => count($batches),
                    'items_count' => count($allResults),
                    'usage' => $totalUsage,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('NissayaTranslate: 批量翻译失败', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ];
        }
    }
}
