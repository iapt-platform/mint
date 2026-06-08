<?php

namespace App\Services\AIAssistant;

use App\Http\Resources\AiModelResource;
use App\Services\NissayaParser;
use App\Services\OpenAIService;
use App\Services\RomanizeService;
use Illuminate\Support\Facades\Log;

class NissayaTranslateService
{
    protected AiModelResource $model;

    protected bool $romanize;

    protected bool $thinking;

    /**
     * 翻译提示词模板
     */
    protected string $translatePrompt = <<<'PROMPT'
你是一个专业的缅甸语翻译专家。你的任务是将缅文逐词解析(Nissaya)翻译成中文。

输入格式:
- 每行包含三个部分: original(巴利文), translation(缅文译文), note(缅文注释,可能没有)
- 输入为JSON Lines格式

输出要求:
1. 保持巴利文(original)原样输出,不做任何修改
2. 将巴利文(original)直接翻译成中文
2. 将缅文译文(translation)翻译成中文
3. 将缅文注释(note)翻译成中文
4. 输出必须是严格的JSON Lines格式,每行一个有效的JSON对象
5. 不要添加任何解释、说明或markdown代码块标记
6. 保持原有的数据结构和字段名称
7. 输出三个字段
    1. original:原巴利文
    2. translation:巴利文的中文译文>缅文的中文译文
    3. note:缅文注释的中文译文
    3. confidence:两个译文的语义相似度(0-100)

示例输入:
{"original":"buddha","translation":"ဗုဒ္ဓ","note":"အဘိဓာန်"}

示例输出:
{"original":"buddha","translation":"佛>佛陀","note":"词汇表","confidence":100}

请翻译以下内容:
PROMPT;

    public function __construct(
        protected OpenAIService $openAIService,
        protected NissayaParser $nissayaParser,
        protected RomanizeService $romanizeService
    ) {
        $this->romanize = true;
    }

    /**
     * 设置模型配置
     */
    public function setModel(AiModelResource $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * 设置模型配置
     */
    public function setThinking(?bool $thinking): self
    {
        if ($thinking === null) {
            return $this;
        }
        $this->thinking = $thinking;

        return $this;
    }

    /**
     * 设置翻译提示词
     */
    public function setTranslatePrompt(string $prompt): self
    {
        $this->translatePrompt = $prompt;

        return $this;
    }

    /**
     * 设置翻译提示词
     *
     * @param  string  $prompt
     */
    public function setRomanize(bool $romanize): self
    {
        $this->romanize = $romanize;

        return $this;
    }

    /**
     * 翻译缅文版逐词解析
     *
     * @param  string  $text  格式: 巴利文=缅文
     * @param  bool  $stream  是否流式输出
     *
     * @throws \Exception
     */
    public function translate(string $text, bool $stream = false): array
    {
        $startAt = time();

        try {
            // 1. 解析nissaya文本为数组
            $parsedData = $this->nissayaParser->parse($text);

            if (empty($parsedData)) {
                throw new \Exception('解析nissaya文本失败,返回空数组');
            }

            $parsedData = $this->romanize($parsedData);

            foreach ($parsedData as $key => $value) {
                if (isset($value['notes']) && is_array($value['notes'])) {
                    $parsedData[$key]['note'] = implode("\n\n----\n\n", $value['notes']);
                    $parsedData[$key]['note'] = str_replace("\n**", "\n\n-----\n\n", $parsedData[$key]['note']);
                    unset($parsedData[$key]['notes']);
                }
            }

            // 2. 将解析后的数组转换为JSONL格式
            $jsonlInput = $this->arrayToJsonl($parsedData);

            Log::info('NissayaTranslate: 准备翻译', [
                'items_count' => count($parsedData),
                'input_length' => strlen($jsonlInput),
            ]);

            // 3. 调用LLM进行翻译
            $response = $this->openAIService
                ->setApiUrl($this->model['url'])
                ->setModel($this->model['model'])
                ->setApiKey($this->model['key'])
                ->setSystemPrompt($this->translatePrompt)
                ->setTemperature(0.3)
                ->setStream($stream)
                ->setThinking($this->thinking)
                ->send($jsonlInput);

            $complete = time() - $startAt;
            $content = $response['choices'][0]['message']['content'] ?? '';

            if (empty($content)) {
                throw new \Exception('LLM返回内容为空');
            }

            // 4. 解析JSONL格式的翻译结果
            $translatedData = $this->jsonlToArray($content);

            Log::debug('NissayaTranslate: 翻译完成', [
                'duration' => $complete,
                'output_items' => count($translatedData),
                'input_tokens' => $response['usage']['prompt_tokens'] ?? 0,
                'output_tokens' => $response['usage']['completion_tokens'] ?? 0,
            ]);

            return [
                'success' => true,
                'data' => $translatedData,
                'meta' => [
                    'duration' => $complete,
                    'items_count' => count($translatedData),
                    'usage' => $response['usage'] ?? [],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('NissayaTranslate: 翻译失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    protected function romanize(array $data): array
    {
        if ($this->romanize) {
            foreach ($data as $key => $value) {
                $data[$key]['original'] = $this->romanizeService->myanmarToRoman($value['original']);
            }
        }

        return $data;
    }

    /**
     * 将数组转换为JSONL格式
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
                Log::warning('NissayaTranslate: 无法解析JSON行', [
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
     * @param  int  $batchSize  每批处理的条目数
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
                Log::debug('NissayaTranslate: 处理批次 '.($index + 1).'/'.count($batches));

                $jsonlInput = $this->arrayToJsonl($batch);
                $response = $this->openAIService
                    ->setApiUrl($this->model['url'])
                    ->setModel($this->model['model'])
                    ->setApiKey($this->model['key'])
                    ->setSystemPrompt($this->translatePrompt)
                    ->setTemperature(0.7)
                    ->setStream(false)
                    ->setThinking($this->thinking)
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
