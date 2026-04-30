<?php

namespace App\Services\Template\Renderers;

use Illuminate\Support\Str;

use App\Services\Template\Contracts\RendererInterface;
use App\Services\Template\ParsedDocument;
use App\Services\Template\ContentNode;
use App\Services\Template\TextNode;
use App\Services\Template\MarkdownNode;
use App\Services\Template\TemplateNode;



// ================== HTML 渲染器 ==================

class HtmlRenderer implements RendererInterface
{
    private array $templateMappings = [];

    public function __construct()
    {
        $this->initializeTemplateMappings();
    }

    private function initializeTemplateMappings(): void
    {
        $this->templateMappings = [
            'note' => function ($params) {
                $type = $params['type'] ?? 'info';
                $text = $params['text'] ?? '';
                $title = $params['title'] ?? '';

                $typeClass = match ($type) {
                    'warning' => 'alert-warning',
                    'error' => 'alert-danger',
                    'success' => 'alert-success',
                    default => 'alert-info'
                };

                $titleHtml = $title ? "<h6 class='alert-heading'>$title</h6>" : '';
                return "<div class='alert $typeClass' role='alert'>$titleHtml$text</div>";
            },

            'info' => function ($params) {
                $content = $params['content'] ?? '';
                $title = $params['title'] ?? '';

                $titleHtml = $title ? "<h6 class='alert-heading'>$title</h6>" : '';
                return "<div class='alert alert-info' role='alert'>$titleHtml$content</div>";
            },

            'warning' => function ($params) {
                $message = $params['message'] ?? '';
                $title = $params['title'] ?? '';

                $titleHtml = $title ? "<h6 class='alert-heading'>$title</h6>" : '';
                return "<div class='alert alert-warning' role='alert'>$titleHtml$message</div>";
            }
        ];
    }

    public function render(ParsedDocument $document): string
    {
        $html = '';

        foreach ($document->content as $node) {
            $html .= $this->renderNode($node);
        }

        return Str::markdown($html);
    }

    private function renderNode(ContentNode $node): string
    {
        switch ($node->type) {
            case 'text':
                return $this->renderText($node);
            case 'markdown':
                return $this->renderMarkdown($node);
            case 'template':
                return $this->renderTemplate($node);
            default:
                return '';
        }
    }

    public function renderText(TextNode $text): string
    {
        return htmlspecialchars($text->content, ENT_QUOTES, 'UTF-8');
    }

    private function renderMarkdown(MarkdownNode $markdown): string
    {
        return Str::markdown($markdown->content);
        // 简单的 Markdown 渲染，实际项目中可以使用 CommonMark 等库
        $html = $markdown->content;

        // 处理粗体
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);

        // 处理链接
        $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);

        return $html;
    }

    public function renderTemplate(TemplateNode $template): string
    {
        if (!isset($this->templateMappings[$template->name])) {
            // 未知模板，返回原始内容
            return htmlspecialchars($template->raw, ENT_QUOTES, 'UTF-8');
        }

        $renderer = $this->templateMappings[$template->name];

        // 处理参数中的嵌套内容
        $processedParams = [];
        foreach ($template->parameters as $key => $value) {
            if (is_array($value)) {
                // 嵌套内容，递归渲染
                $nestedHtml = '';
                foreach ($value as $childNode) {
                    $nestedHtml .= $this->renderNode($childNode);
                }
                $processedParams[$key] = $nestedHtml;
            } else {
                $processedParams[$key] = $value;
            }
        }

        return $renderer($processedParams);
    }

    public function registerTemplate(string $name, callable $renderer): void
    {
        $this->templateMappings[$name] = $renderer;
    }
}
