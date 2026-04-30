<?php

namespace App\Services\Template\Renderers;

use App\Services\Template\Contracts\RendererInterface;
use App\Services\Template\ParsedDocument;
use App\Services\Template\ContentNode;
use App\Services\Template\TextNode;
use App\Services\Template\MarkdownNode;
use App\Services\Template\TemplateNode;




// ================== Markdown 渲染器 ==================

class MarkdownRenderer implements RendererInterface
{
    public function render(ParsedDocument $document): string
    {
        $markdown = '';

        foreach ($document->content as $node) {
            $markdown .= $this->renderNode($node);
        }

        return $markdown;
    }

    private function renderNode(ContentNode $node): string
    {
        switch ($node->type) {
            case 'text':
                return $this->renderText($node);
            case 'markdown':
                return $node->content;
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

    public function renderTemplate(TemplateNode $template): string
    {
        // 将模板转换回 Markdown 格式
        $params = [];

        foreach ($template->parameters as $key => $value) {
            if (is_array($value)) {
                // 嵌套内容，递归渲染
                $nestedMarkdown = '';
                foreach ($value as $childNode) {
                    $nestedMarkdown .= $this->renderNode($childNode);
                }
                $params[] = "$key=$nestedMarkdown";
            } else {
                $params[] = is_numeric($key) ? $value : "$key=$value";
            }
        }

        $paramString = implode('|', $params);
        return "{{" . $template->name . ($paramString ? "|$paramString" : "") . "}}";
    }
}
