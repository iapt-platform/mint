<?php

namespace App\Services\Markup\Render;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;

/**
 * IR → JSON 节点树（v3 API 下发给 dashboard-v6 / wikipali-mobile）。
 *
 * 三种节点：
 * - `{type: 'text', value}`：纯文本，前端直接作为 React 文本节点（自动转义）
 * - `{type: 'el', tag, attrs?, children}`：HTML 元素，tag 与 attrs 已过白名单
 * - `{type: 'tpl', name, props, children}`：模板，name 是前端组件名；props 里嵌套的内容（术语 note、句子正文）也是节点数组
 *
 * 单段落的内容（如一句译文）直接给出段落里的行内节点，不包 `p`。
 */
final class TreeRenderer implements Renderer
{
    /**
     * @return list<array<string, mixed>>
     */
    public function document(Document $document, RenderContext $ctx): array
    {
        return $this->nodes($document->nodes, $ctx);
    }

    /**
     * @param  list<Node>  $nodes
     * @return list<array<string, mixed>>
     */
    private function nodes(array $nodes, RenderContext $ctx): array
    {
        $out = [];
        foreach ($nodes as $node) {
            if (! $node instanceof Text && ! $node instanceof Element && ! $node instanceof Call) {
                continue;   // 构建期的辅助节点（如配不上对的批注边界）不输出
            }
            $rendered = $this->node($node, $ctx);
            $last = array_key_last($out);
            // 相邻文本并成一个节点
            if ($rendered['type'] === 'text' && $last !== null && $out[$last]['type'] === 'text') {
                $out[$last]['value'] .= $rendered['value'];

                continue;
            }
            $out[] = $rendered;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function node(Node $node, RenderContext $ctx): array
    {
        if ($node instanceof Text) {
            return ['type' => 'text', 'value' => $node->value];
        }
        if ($node instanceof Element) {
            $element = ['type' => 'el', 'tag' => $node->tag];
            if ($node->attrs !== []) {
                $element['attrs'] = $node->attrs;
            }
            $element['children'] = $this->nodes($node->children, $ctx);

            return $element;
        }

        /** @var Call $node */
        $children = $node->template->children($node, $ctx);

        return [
            'type' => 'tpl',
            'name' => $node->template->component($node, $ctx),
            'props' => (object) $this->props($node->template->props($node, $ctx), $ctx),
            'children' => $children === null ? [] : $this->document($children, $ctx),
        ];
    }

    /**
     * props 里的 Document 展开成节点数组（递归到数组里）。
     *
     * @param  array<array-key, mixed>  $props
     * @return array<array-key, mixed>
     */
    private function props(array $props, RenderContext $ctx): array
    {
        foreach ($props as $key => $value) {
            if ($value instanceof Document) {
                $props[$key] = $this->document($value, $ctx);
            } elseif (is_array($value)) {
                $props[$key] = $this->props($value, $ctx);
            }
        }

        return $props;
    }
}
