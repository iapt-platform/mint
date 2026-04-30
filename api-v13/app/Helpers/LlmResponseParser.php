<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/**
 * Class LlmResponseParser
 * @package App\Helpers
 */
class LlmResponseParser
{
    /**
     * 解析LLM返回的可能包含Markdown格式（如```json...```）或额外文字说明的JSON字符串。
     *
     * @param string $input LLM返回的原始字符串。
     * @return array|null 解析成功的PHP数组，如果解析失败则返回空数组
     */
    public static function json(string $input): array
    {
        // 1. 预处理：查找并提取被 Markdown 代码块包裹的JSON字符串。
        // 匹配 ```json ... ``` 或 ``` ... ``` 格式的代码块。
        // S: dotall 模式，允许 . 匹配换行符。
        // ?: 非贪婪模式，匹配尽可能少的字符直到遇到下一个 ```。
        $pattern = '/```(?:json)?\s*(.*?)\s*```/s';

        if (preg_match($pattern, $input, $matches)) {
            // 情况 2 和 3：找到代码块，提取其内容。
            // $matches[1] 包含代码块内部的内容。
            $jsonString = trim($matches[1]);
        } else {
            // 情况 1：没有代码块包裹，认为整个输入就是纯 JSON 字符串。
            // 这种情况也包含了用户可能提供的、未被代码块包裹但带有文字说明的JSON。
            // 这种情况下，我们需要在后续尝试解析时，先尝试 trim() 整个输入。
            $jsonString = trim($input);
        }

        // 2. 尝试解析提取或预处理后的字符串。
        // json_decode 的第二个参数设为 true，将其解码为关联数组。
        $data = json_decode($jsonString, true);

        // 3. 检查解析结果。
        // json_last_error() 返回 JSON 解析的最后一个错误代码。
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            // 解析成功且结果为数组。
            return $data;
        }

        // 4. 如果第一次尝试失败 (通常是针对情况 1 或包含文字说明的情况)，
        // 并且提取的字符串与原始输入相同（即没有匹配到代码块），
        // 并且原始输入中包含可能干扰解析的文字，
        // 则可以考虑更复杂的清理步骤，但考虑到 LLM 返回的 JSON 通常比较规范，
        // 推荐的做法是如果第一次没有成功，就返回 null，以保持函数的健壮性。
        // 纯粹的 JSON 字符串（情况 1）在第一次尝试时应该已经成功。

        // 如果解析失败，则返回 空数据
        Log::error('JSON 解析失败', [
            'error' => json_last_error_msg(),
            'input_preview' => mb_substr($input, 0, 500),
        ]);
        return [];
    }

    /**
     * 解析LLM返回的JSONL（JSON Lines）格式字符串。
     * 支持Markdown代码块包裹、额外说明文字，以及处理截断的JSONL数据。
     * 每一行应为独立的JSON对象，解析时会跳过无效行并返回所有成功解析的数据。
     *
     * @param string $input LLM返回的原始JSONL字符串。
     * @return array 解析成功的PHP数组，每个元素对应一行有效的JSON对象。如果完全解析失败则返回空数组。
     */
    public static function jsonl(string $input): array
    {
        // 1. 预处理：查找并提取被 Markdown 代码块包裹的 JSONL 字符串。
        // 匹配 ```jsonl ... ``` 或 ``` ... ``` 格式的代码块。
        $pattern = '/```(?:jsonl?)?\s*(.*?)\s*```/s';

        if (preg_match($pattern, $input, $matches)) {
            // 情况 2 和 3：找到代码块，提取其内容。
            $jsonlString = trim($matches[1]);
        } else {
            // 情况 1：没有代码块包裹，认为整个输入就是纯 JSONL 字符串。
            $jsonlString = trim($input);
        }

        // 2. 按行分割 JSONL 字符串。
        // 使用 PHP_EOL 或 \n 作为分隔符，同时处理 Windows (\r\n) 和 Unix (\n) 换行符。
        $lines = preg_split('/\r\n|\r|\n/', $jsonlString);

        // 3. 逐行解析 JSON 对象。
        $result = [];
        foreach ($lines as $lineNumber => $line) {
            // 去除每行的首尾空白字符。
            $line = trim($line);

            // 跳过空行。
            if (empty($line)) {
                continue;
            }

            // 尝试解析当前行为 JSON。
            $decoded = json_decode($line, true);

            // 检查解析是否成功。
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // 解析成功，添加到结果数组。
                $result[] = $decoded;
            } else {
                // 解析失败，记录错误日志（可选）。
                // 这里处理了截断的情况：如果某行不是有效 JSON，则跳过它。
                // 通常最后一行可能因截断而无效，前面的有效行仍会被返回。
                Log::warning("JSONL解析失败 - 行 " . ($lineNumber + 1) . ": " . $line);
            }
        }

        // 4. 返回解析结果。
        // 即使没有成功解析任何行，也返回空数组而非 null，保持返回类型一致。
        if (empty($result)) {
            Log::error('JSONL解析失败，未能提取任何有效数据: ' . $input);
        }

        return $result;
    }

    public static function jsonl_encode(array $input): string
    {
        $rows = [];
        foreach ($input as $key => $value) {
            $rows[] = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return implode("\n", $rows);
    }
}
