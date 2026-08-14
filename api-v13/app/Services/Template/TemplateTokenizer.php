<?php

namespace App\Services\Template;

// ================== 词法分析器 ==================

class TemplateTokenizer
{
    private string $content;

    private int $position = 0;

    private int $length;

    public function __construct(string $content)
    {
        $this->content = $content;
        $this->length = strlen($content);
    }

    public function tokenize(): array
    {
        $tokens = [];
        $this->position = 0;

        while ($this->position < $this->length) {
            $token = $this->nextToken();
            if ($token) {
                $tokens[] = $token;
            }
        }

        return $tokens;
    }

    private function nextToken(): ?array
    {
        // 寻找模板开始标记
        $templateStart = strpos($this->content, '{{', $this->position);

        if ($templateStart === false) {
            // 没有更多模板，返回剩余文本
            if ($this->position < $this->length) {
                $text = substr($this->content, $this->position);
                $this->position = $this->length;

                return [
                    'type' => 'text',
                    'content' => $text,
                    'position' => ['start' => $this->position - strlen($text), 'end' => $this->position],
                ];
            }

            return null;
        }

        // 如果模板前有文本
        if ($templateStart > $this->position) {
            $text = substr($this->content, $this->position, $templateStart - $this->position);
            $this->position = $templateStart;

            return [
                'type' => 'text',
                'content' => $text,
                'position' => ['start' => $this->position - strlen($text), 'end' => $this->position],
            ];
        }

        // 解析模板
        return $this->parseTemplate();
    }

    private function parseTemplate(): ?array
    {
        $start = $this->position;
        $this->position += 2; // 跳过 '{{'

        $braceCount = 1;
        $templateContent = '';

        while ($this->position < $this->length && $braceCount > 0) {
            $char = $this->content[$this->position];
            $nextChar = $this->position + 1 < $this->length ? $this->content[$this->position + 1] : '';

            if ($char === '{' && $nextChar === '{') {
                $braceCount++;
                $templateContent .= '{{';
                $this->position += 2;
            } elseif ($char === '}' && $nextChar === '}') {
                $braceCount--;
                if ($braceCount > 0) {
                    $templateContent .= '}}';
                }
                $this->position += 2;
            } else {
                $templateContent .= $char;
                $this->position++;
            }
        }

        if ($braceCount > 0) {
            // 未闭合的模板，当作普通文本处理
            $this->position = $start + 2;

            return [
                'type' => 'text',
                'content' => '{{',
                'position' => ['start' => $start, 'end' => $start + 2],
            ];
        }

        return [
            'type' => 'template',
            'content' => trim($templateContent),
            'raw' => '{{'.$templateContent.'}}',
            'position' => ['start' => $start, 'end' => $this->position],
        ];
    }
}
