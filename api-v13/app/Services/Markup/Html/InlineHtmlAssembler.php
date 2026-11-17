<?php

namespace App\Services\Markup\Html;

use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;

/**
 * 把行内节点序列里散落的 HTML 开 / 闭标签重新配成嵌套元素。
 *
 * 闭标签找栈里最近的同名元素，中间没闭合的一并闭合；找不到的闭标签忽略；到结尾仍未闭合的就地闭合。
 * 被 {@see HtmlPolicy} 丢掉的标签，直到它闭合为止的内容也一起丢掉。
 */
final class InlineHtmlAssembler
{
    /** @var list<Node> */
    private array $root = [];

    /** @var list<array{tag: string, element: Element|null}> element 为 null 表示被丢掉的标签 */
    private array $stack = [];

    public function add(Node $node): void
    {
        foreach ($this->stack as $frame) {
            if ($frame['element'] === null) {
                return;
            }
        }
        $top = end($this->stack);
        if ($top === false) {
            $this->root[] = $node;
        } else {
            $top['element']->children[] = $node;
        }
    }

    public function tag(string $literal): void
    {
        $tag = HtmlFragment::tagLiteral($literal);
        if ($tag['kind'] === 'close') {
            $this->close($tag['tag']);

            return;
        }
        if ($tag['kind'] !== 'open') {
            return;
        }
        $element = HtmlFragment::element($tag['tag'], $tag['attrs']);
        $void = $tag['selfClosing'] || in_array($tag['tag'], HtmlPolicy::VOID, true);
        if ($element === false) {
            if (! $void) {
                $this->stack[] = ['tag' => $tag['tag'], 'element' => null];
            }

            return;
        }
        $this->add($element);
        if (! $void) {
            $this->stack[] = ['tag' => $tag['tag'], 'element' => $element];
        }
    }

    private function close(string $tag): void
    {
        for ($i = count($this->stack) - 1; $i >= 0; $i--) {
            if ($this->stack[$i]['tag'] === $tag) {
                $this->stack = array_slice($this->stack, 0, $i);

                return;
            }
        }
    }

    /**
     * @return list<Node>
     */
    public function nodes(): array
    {
        return $this->root;
    }
}
