<?php

namespace App\Services\Markup\Render;

use App\Services\Markup\Html\HtmlPolicy;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;

/**
 * IR → 静态 HTML（library 阅读器、导出）。
 *
 * 单段落内容包成 `<span>`（v2 的 replaceSinglePWithSpan）；站内绝对路径的图片补上站点地址。
 * 模板的 HTML 由各模板的 html() 给出。
 */
final class HtmlRenderer implements Renderer
{
    public function document(Document $document, RenderContext $ctx): string
    {
        $html = $this->nodes($document->nodes, $ctx);

        return $document->inline && ! $document->fragment && $html !== '' ? "<span>{$html}</span>" : $html;
    }

    /** 行内安全渲染时要换成 span 的块级标签 */
    private const BLOCK_TAGS = [
        'p', 'div', 'blockquote', 'pre', 'ul', 'ol', 'li', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'dl', 'dt', 'dd', 'figure', 'figcaption', 'details', 'summary',
    ];

    private bool $inlineOnly = false;

    /**
     * 渲染成只含行内元素的 HTML：块级标签换成 `<span class="md-{标签}">`，顶层块之间用 `<br />` 分隔。
     *
     * 用于嵌在句子中间的内容（脚注 sidenote）：块级元素落进 `<p>` / `<span>` 里，浏览器会提前闭合外层，
     * 把 sidenote 的结构切断、吞掉后面的正文。
     */
    public function inlineSafe(Document $document, RenderContext $ctx): string
    {
        $previous = $this->inlineOnly;
        $this->inlineOnly = true;
        try {
            if ($document->inline) {
                return $this->document($document, $ctx);
            }
            $blocks = array_map(fn (Node $node) => $this->nodes([$node], $ctx), $document->nodes);
            $html = implode('<br />', array_filter($blocks, fn (string $html) => $html !== ''));

            return $html === '' ? '' : "<span>{$html}</span>";
        } finally {
            $this->inlineOnly = $previous;
        }
    }

    /**
     * @param  list<Node>  $nodes
     */
    public function nodes(array $nodes, RenderContext $ctx): string
    {
        $html = '';
        foreach ($nodes as $node) {
            $html .= match (true) {
                $node instanceof Text => e($node->value, false),
                $node instanceof Element => $this->element($node, $ctx),
                $node instanceof Call => $node->template->html($node, $ctx),
                default => '',
            };
        }

        return $html;
    }

    private function element(Element $element, RenderContext $ctx): string
    {
        $attrs = '';
        foreach ($element->attrs as $name => $value) {
            if ($name === 'src' && str_starts_with($value, '/') && ! str_starts_with($value, '//')) {
                $value = rtrim((string) config('app.url'), '/').$value;
            }
            $attrs .= ' '.$name.'="'.e($value, false).'"';
        }
        if ($this->inlineOnly && in_array($element->tag, self::BLOCK_TAGS, true)) {
            if ($element->tag === 'hr') {
                return '<br />';
            }
            $class = 'md-'.$element->tag.(isset($element->attrs['class']) ? ' '.e($element->attrs['class'], false) : '');

            return '<span class="'.$class.'">'.$this->nodes($element->children, $ctx).'</span>';
        }
        if (in_array($element->tag, [...HtmlPolicy::VOID, 'input'], true)) {
            return "<{$element->tag}{$attrs} />";
        }
        // 段落里只有块级模板（目录、mermaid）：不包 <p>，否则 <p><nav> 会被浏览器提前闭合
        if ($element->tag === 'p' && $this->onlyBlockTemplates($element->children)) {
            return $this->nodes($element->children, $ctx);
        }

        return "<{$element->tag}{$attrs}>".$this->nodes($element->children, $ctx)."</{$element->tag}>";
    }

    /**
     * @param  list<Node>  $nodes
     */
    private function onlyBlockTemplates(array $nodes): bool
    {
        $found = false;
        foreach ($nodes as $node) {
            if ($node instanceof Text && trim($node->value) === '') {
                continue;
            }
            if (! $node instanceof Call || ! $node->template->block()) {
                return false;
            }
            $found = true;
        }

        return $found;
    }

    /**
     * 文末的脚注列表（footnote 选项打开时）。
     */
    public function footnotes(RenderContext $ctx): string
    {
        if ($ctx->footnotes === []) {
            return '';
        }
        $html = '<div><h1>endnote</h1>';
        foreach ($ctx->footnotes as $note) {
            $html .= '<p><a name="footnote-'.$note['sn'].'">['.$note['sn'].']</a> '.$note['content'].'</p>';
        }

        return $html.'</div>';
    }
}
