<?php

namespace App\Services\Markup\Render;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;

/**
 * IR → 纯文本（text / simple / prompt）。块与块之间换行，模板由各模板的 plain() 给出。
 */
final class TextRenderer implements Renderer
{
    private const BLOCKS = [
        'p', 'div', 'blockquote', 'pre', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tr',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'dl', 'dt', 'dd', 'figure', 'details', 'summary',
    ];

    public function __construct(private readonly Format $format) {}

    public function document(Document $document, RenderContext $ctx): string
    {
        if ($document->inline) {
            return $this->nodes($document->nodes, $ctx);
        }
        // 块级内容逐块换行（块级模板如 mermaid 自己不带换行）
        $text = '';
        foreach ($document->nodes as $node) {
            $text .= rtrim($this->nodes([$node], $ctx), "\n")."\n";
        }

        return rtrim($text, "\n");
    }

    /**
     * @param  list<Node>  $nodes
     */
    private function nodes(array $nodes, RenderContext $ctx): string
    {
        $text = '';
        foreach ($nodes as $node) {
            if ($node instanceof Text) {
                $text .= $node->value;
            } elseif ($node instanceof Call) {
                $text .= $node->template->plain($node, $ctx, $this->format);
            } elseif ($node instanceof Element) {
                $text .= $this->element($node, $ctx);
            }
        }

        return $text;
    }

    private function element(Element $element, RenderContext $ctx): string
    {
        if ($element->tag === 'br') {
            return "\n";
        }
        if ($element->tag === 'img') {
            return $element->attrs['alt'] ?? '';
        }
        $inner = $this->nodes($element->children, $ctx);
        if ($element->tag === 'td' || $element->tag === 'th') {
            return $inner."\t";
        }

        return in_array($element->tag, self::BLOCKS, true) ? rtrim($inner, "\n")."\n" : $inner;
    }
}
