<?php

namespace App\Services\Template\Renderers;

use App\Services\Template\Contracts\RendererInterface;
use App\Services\Template\ParsedDocument;
use App\Services\Template\ContentNode;
use App\Services\Template\TextNode;
use App\Services\Template\MarkdownNode;
use App\Services\Template\TemplateNode;



// ================== 纯文本渲染器 ==================

class TextRenderer implements RendererInterface
{
    private array $templateTexts = [];

    public function __construct()
    {
        $this->initializeTemplateTexts();
    }

    private function initializeTemplateTexts(): void
    {
        $this->templateTexts = [
            'note' => function ($params) {
                $type = $params['type'] ?? 'info';
                $text = $params['text'] ?? '';
                $title = $params['title'] ?? '';

                $prefix = match ($type) {
                    'warning' => '[警告]',
                    'error' => '[错误]',
                    'success' => '[成功]',
                    default => '[信息]'
                };

                return $prefix . ($title ? " $title: " : ' ') . $text;
            },

            'info' => function ($params) {
                $content = $params['content'] ?? '';
                $title = $params['title'] ?? '';

                return '[信息]' . ($title ? " $title: " : ' ') . $content;
            },

            'warning' => function ($params) {
                $message = $params['message'] ?? '';
                $title = $params['title'] ?? '';

                return '[警告]' . ($title ? " $title: " : ' ') . $message;
            }
        ];
    }

    public function render(ParsedDocument $document): string
    {
        $text = '';

        foreach ($document->content as $node) {
            $text .= $this->renderNode($node);
        }

        return $text;
    }

    private function renderNode(ContentNode $node): string
    {
        switch ($node->type) {
            case 'text':
                return $this->renderText($node);
            case 'markdown':
                return $this->renderMarkdownAsText($node);
            case 'template':
                return $this->renderTemplate($node);
            default:
                return '';
        }
    }

    public function renderText(TextNode $text): string
    {
        return $text->content;
    }

    private function renderMarkdownAsText(MarkdownNode $markdown): string
    {
        // 移除 Markdown 标记，返回纯文本
        $text = $markdown->content;

        // 移除粗体和斜体标记
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        $text = preg_replace('/\*(.*?)\*/', '$1', $text);

        // 移除链接标记，保留链接文本
        $text = preg_replace('/\[([^\]]+)\]\([^)]+\)/', '$1', $text);

        return $text;
    }

    public function renderTemplate(TemplateNode $template): string
    {
        if (!isset($this->templateTexts[$template->name])) {
            // 未知模板，返回简化的文本表示
            $text = $template->name;
            if (!empty($template->parameters)) {
                $paramTexts = [];
                foreach ($template->parameters as $key => $value) {
                    if (is_array($value)) {
                        $nestedText = '';
                        foreach ($value as $childNode) {
                            $nestedText .= $this->renderNode($childNode);
                        }
                        $paramTexts[] = is_numeric($key) ? $nestedText : "$key: $nestedText";
                    } else {
                        $paramTexts[] = is_numeric($key) ? $value : "$key: $value";
                    }
                }
                $text .= '(' . implode(', ', $paramTexts) . ')';
            }
            return $text;
        }

        $renderer = $this->templateTexts[$template->name];

        // 处理参数中的嵌套内容
        $processedParams = [];
        foreach ($template->parameters as $key => $value) {
            if (is_array($value)) {
                $nestedText = '';
                foreach ($value as $childNode) {
                    $nestedText .= $this->renderNode($childNode);
                }
                $processedParams[$key] = $nestedText;
            } else {
                $processedParams[$key] = $value;
            }
        }

        return $renderer($processedParams);
    }

    public function registerTemplate(string $name, callable $renderer): void
    {
        $this->templateTexts[$name] = $renderer;
    }
}
