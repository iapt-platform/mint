<?php

namespace App\Services\Template\Renderers;

use App\Services\Template\Contracts\RendererInterface;
use App\Services\Template\ParsedDocument;
use App\Services\Template\TemplateNode;
use App\Services\Template\TextNode;

// ================== JSON 渲染器 ==================

class JsonRenderer implements RendererInterface
{
    public function render(ParsedDocument $document): string
    {
        $data = [
            'type' => $document->type,
            'content' => array_map(fn ($node) => $node->toArray(), $document->content),
            'meta' => $document->meta,
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function renderTemplate(TemplateNode $template): string
    {
        return json_encode($template->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function renderText(TextNode $text): string
    {
        return json_encode($text->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
