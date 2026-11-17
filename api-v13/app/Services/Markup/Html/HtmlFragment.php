<?php

namespace App\Services\Markup\Html;

use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;
use Closure;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * 原始 HTML → IR 节点（过 {@see HtmlPolicy}）。
 *
 * 两种来源：
 * - HTML 块（CommonMark 的 HtmlBlock）：整块交给 DOMDocument 解析
 * - 行内 HTML（HtmlInline）：CommonMark 把 `<u>a</u>` 拆成「开标签、文字、闭标签」三个兄弟节点，
 *   由 {@see InlineHtmlAssembler} 按栈重新配对
 */
final class HtmlFragment
{
    /**
     * @param  Closure(string): list<Node>  $expandText  文字 → 节点（展开其中的模板占位符）
     * @return list<Node>
     */
    public static function parseBlock(string $html, Closure $expandText): array
    {
        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML(
            '<?xml encoding="UTF-8"><div>'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = null;
        foreach ($doc->childNodes as $child) {
            if ($child instanceof DOMElement) {
                $root = $child;
                break;
            }
        }

        return $root === null ? [] : self::children($root, $expandText);
    }

    /**
     * @param  Closure(string): list<Node>  $expandText
     * @return list<Node>
     */
    private static function children(DOMNode $parent, Closure $expandText): array
    {
        $nodes = [];
        foreach ($parent->childNodes as $child) {
            if ($child instanceof DOMText) {
                array_push($nodes, ...$expandText($child->data));
            } elseif ($child instanceof DOMElement) {
                $element = self::element($child->tagName, self::domAttrs($child));
                if ($element === false) {
                    continue;
                }
                $element->children = self::children($child, $expandText);
                $nodes[] = $element;
            }
        }

        return $nodes;
    }

    /**
     * 按白名单建元素；false 表示连同内容丢掉。
     *
     * @param  array<string, string>  $attrs
     */
    public static function element(string $tag, array $attrs): Element|false
    {
        $tag = strtolower($tag);
        $decision = HtmlPolicy::tag($tag);
        if ($decision === false) {
            return false;
        }
        if ($decision === null) {
            return new Element('span', ['data-tag' => $tag] + HtmlPolicy::attrs('span', $attrs));
        }

        return new Element($decision, HtmlPolicy::attrs($decision, $attrs));
    }

    /**
     * @return array<string, string>
     */
    private static function domAttrs(DOMElement $element): array
    {
        $attrs = [];
        foreach ($element->attributes as $attr) {
            $attrs[strtolower($attr->nodeName)] = $attr->nodeValue ?? '';
        }

        return $attrs;
    }

    /**
     * 解析一个标签字面量。
     *
     * @return array{kind: 'open'|'close'|'other', tag: string, attrs: array<string, string>, selfClosing: bool}
     */
    public static function tagLiteral(string $literal): array
    {
        if (preg_match('/^<\/([a-zA-Z][a-zA-Z0-9-]*)\s*>$/', $literal, $m) === 1) {
            return ['kind' => 'close', 'tag' => strtolower($m[1]), 'attrs' => [], 'selfClosing' => false];
        }
        if (preg_match('/^<([a-zA-Z][a-zA-Z0-9-]*)(.*?)(\/?)>$/s', $literal, $m) !== 1) {
            return ['kind' => 'other', 'tag' => '', 'attrs' => [], 'selfClosing' => false];
        }
        $attrs = [];
        preg_match_all(
            '/([a-zA-Z_:][-a-zA-Z0-9_:.]*)(?:\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+)))?/',
            $m[2],
            $pairs,
            PREG_SET_ORDER,
        );
        foreach ($pairs as $pair) {
            $value = ($pair[2] ?? '') !== '' ? $pair[2] : ((($pair[3] ?? '') !== '') ? $pair[3] : ($pair[4] ?? ''));
            $attrs[strtolower($pair[1])] = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return ['kind' => 'open', 'tag' => strtolower($m[1]), 'attrs' => $attrs, 'selfClosing' => $m[3] === '/'];
    }

    public static function text(string $value): Text
    {
        return new Text($value);
    }
}
