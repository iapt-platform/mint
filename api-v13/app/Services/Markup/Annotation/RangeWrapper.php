<?php

namespace App\Services\Markup\Annotation;

use App\Services\AnnotationLayout;
use App\Services\Markup\Ir\Boundary;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Scope;

/**
 * 把配对的区间边界（{@see Boundary}）换成包住中间内容的模板调用（issue 高亮）。
 *
 * {@see AnnotationLayout} 保证边界不会切断模板、术语、`**粗体**`、HTML 标签，
 * 但单星号斜体、链接这类没有保护，开、闭可能落在不同层级（一个在 `<em>` 里、一个在外面）。
 * 这时把边界提升到两者最近的共同父层：开头挪到包含它的那个子节点之前，结尾挪到包含它的子节点之后——
 * 相当于把高亮扩展到完整包住那个元素，与 AnnotationLayout「扩展到完整包住」的规则一致。
 *
 * 只在 Element 的 children 里找（模板参数里的内容是另一段 Document，批注不会落进去）。
 */
final class RangeWrapper
{
    /**
     * @param  list<Node>  $nodes  根节点列表（就地修改）
     * @return list<Node>
     */
    public static function apply(array $nodes, Scope $scope): array
    {
        $root = new Element('#root', [], $nodes);
        while (($open = self::find($root, fn (Node $n) => $n instanceof Boundary && $n->open)) !== null) {
            /** @var Boundary $boundary */
            $boundary = self::at($root, $open);
            $close = self::find($root, fn (Node $n) => $n instanceof Boundary && ! $n->open && $n->pair === $boundary->pair);
            if ($close === null || $boundary->call === null || ! self::before($open, $close)) {
                self::remove($root, $open);

                continue;
            }
            self::wrap($root, $open, $close, $boundary, $scope);
        }
        // 剩下的都是配不上对的结尾
        while (($orphan = self::find($root, fn (Node $n) => $n instanceof Boundary)) !== null) {
            self::remove($root, $orphan);
        }

        return $root->children;
    }

    /**
     * @param  list<int>  $open
     * @param  list<int>  $close
     */
    private static function wrap(Element $root, array $open, array $close, Boundary $boundary, Scope $scope): void
    {
        // 最近的共同父层：两条路径在第 k 层分叉（或其中一条在第 k 层就是边界本身）
        $k = 0;
        while ($k < count($open) - 1 && $k < count($close) - 1 && $open[$k] === $close[$k]) {
            $k++;
        }
        $container = $root;
        for ($i = 0; $i < $k; $i++) {
            /** @var Element $container */
            $container = $container->children[$open[$i]];
        }
        $from = $open[$k];
        $to = $close[$k];
        $openIsDirect = count($open) === $k + 1;
        $closeIsDirect = count($close) === $k + 1;

        // 先删深处的边界（不影响第 k 层的下标），再处理第 k 层
        if (! $closeIsDirect) {
            self::remove($root, $close);
        }
        if (! $openIsDirect) {
            self::remove($root, $open);
        }
        $start = $openIsDirect ? $from + 1 : $from;
        $end = $closeIsDirect ? $to - 1 : $to;
        $inner = array_slice($container->children, $start, max(0, $end - $start + 1));

        $call = $boundary->call;
        $call->slots['content'] = new Document($inner, true, '', [], $scope, fragment: true);
        array_splice($container->children, $from, $to - $from + 1, [$call]);
    }

    /**
     * 深度优先找第一个满足条件的节点，返回下标路径。
     *
     * @param  callable(Node): bool  $match
     * @return list<int>|null
     */
    private static function find(Element $element, callable $match): ?array
    {
        foreach ($element->children as $i => $child) {
            if ($match($child)) {
                return [$i];
            }
            if ($child instanceof Element && ($path = self::find($child, $match)) !== null) {
                return [$i, ...$path];
            }
        }

        return null;
    }

    /**
     * @param  list<int>  $path
     */
    private static function at(Element $root, array $path): Node
    {
        $node = $root;
        foreach ($path as $i) {
            /** @var Element $node */
            $node = $node->children[$i];
        }

        return $node;
    }

    /**
     * @param  list<int>  $path
     */
    private static function remove(Element $root, array $path): void
    {
        $parent = $root;
        foreach (array_slice($path, 0, -1) as $i) {
            /** @var Element $parent */
            $parent = $parent->children[$i];
        }
        array_splice($parent->children, (int) end($path), 1);
    }

    /**
     * 文档顺序上 a 在 b 之前。
     *
     * @param  list<int>  $a
     * @param  list<int>  $b
     */
    private static function before(array $a, array $b): bool
    {
        // 逐层比下标（PHP 的数组 < 先比长度，不是字典序）
        foreach ($a as $depth => $index) {
            if (! isset($b[$depth])) {
                return false;
            }
            if ($index !== $b[$depth]) {
                return $index < $b[$depth];
            }
        }

        return count($a) < count($b);
    }
}
