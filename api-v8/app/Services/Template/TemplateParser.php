<?php

// ================== 主解析器 ==================

namespace App\Services\Template;

use App\Services\Template\Contracts\RendererInterface;
use App\Services\Template\Contracts\ParserInterface;

class TemplateParser implements ParserInterface
{
    private TemplateRegistry $registry;
    private ParameterResolver $parameterResolver;

    public function __construct()
    {
        $this->registry = new TemplateRegistry();
        $this->parameterResolver = new ParameterResolver($this->registry);
    }

    public function parse(string $content): ParsedDocument
    {
        $tokenizer = new TemplateTokenizer($content);
        $tokens = $tokenizer->tokenize();

        $nodes = [];
        $templatesUsed = [];

        foreach ($tokens as $token) {
            if ($token['type'] === 'text') {
                $nodes[] = new TextNode($token['content'], $token['position']);
            } elseif ($token['type'] === 'template') {
                $templateNode = $this->parseTemplateContent($token);
                if ($templateNode) {
                    $nodes[] = $templateNode;
                    $templatesUsed[] = $templateNode->name;
                } else {
                    // 解析失败，当作文本处理
                    $nodes[] = new TextNode($token['raw'], $token['position']);
                }
            }
        }

        return new ParsedDocument($nodes, [
            'templates_used' => array_unique($templatesUsed),
            'total_templates' => count($templatesUsed)
        ]);
    }

    private function parseTemplateContent(array $token): ?TemplateNode
    {
        $content = $token['content'];
        $parts = $this->splitTemplateParts($content);

        if (empty($parts)) {
            return null;
        }

        $templateName = array_shift($parts);
        $rawParams = $this->parseParameters($parts);

        // 递归解析参数中的嵌套模板
        $processedParams = [];
        foreach ($rawParams as $key => $value) {
            if (strpos($value, '{{') !== false) {
                // 参数值包含模板，递归解析
                $subDocument = $this->parse($value);
                $processedParams[$key] = $subDocument->content;
            } else {
                $processedParams[$key] = $value;
            }
        }

        $resolvedParams = $this->parameterResolver->resolveParameters($templateName, $processedParams);

        return new TemplateNode(
            $templateName,
            $resolvedParams,
            [],
            $token['raw'],
            $token['position']
        );
    }

    private function splitTemplateParts(string $content): array
    {
        $parts = [];
        $current = '';
        $braceLevel = 0;
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];
            $nextChar = $i + 1 < strlen($content) ? $content[$i + 1] : '';

            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $quoteChar = '';
                $current .= $char;
            } elseif (!$inQuotes && $char === '{' && $nextChar === '{') {
                $braceLevel++;
                $current .= '{{';
                $i++; // 跳过下一个字符
            } elseif (!$inQuotes && $char === '}' && $nextChar === '}') {
                $braceLevel--;
                $current .= '}}';
                $i++; // 跳过下一个字符
            } elseif (!$inQuotes && $char === '|' && $braceLevel === 0) {
                $parts[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if ($current !== '') {
            $parts[] = trim($current);
        }

        return $parts;
    }

    private function parseParameters(array $parts): array
    {
        $params = [];
        $positionalIndex = 0;

        foreach ($parts as $part) {
            if (strpos($part, '=') !== false && !$this->isInNestedTemplate($part)) {
                // 命名参数
                [$key, $value] = explode('=', $part, 2);
                $params[trim($key)] = trim($value);
            } else {
                // 位置参数
                $params[$positionalIndex] = trim($part);
                $positionalIndex++;
            }
        }

        return $params;
    }

    private function isInNestedTemplate(string $content): bool
    {
        $openBraces = substr_count($content, '{{');
        $closeBraces = substr_count($content, '}}');
        return $openBraces > 0 && $openBraces >= $closeBraces;
    }

    public function getRegistry(): TemplateRegistry
    {
        return $this->registry;
    }
}
