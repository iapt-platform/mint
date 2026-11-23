<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;
use App\Services\Markup\Render\HtmlRenderer;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Render\TextRenderer;
use App\Services\Markup\Templates\Concerns\OwnsBlockTitle;

/**
 * 目录：`{{toc}}`，从本篇内容的标题自动生成，每项链接到对应标题（`#h-…`，IR 构建时生成的稳定 id）。
 *
 * - 层级：缺省收二、三级标题；`{{toc|2-4}}`、`{{toc|2}}` 指定
 * - 标题：目录自带一行标题，规则见 {@see OwnsBlockTitle}（labels.toc；`title=` 改文字或隐藏；
 *   紧跟在手写标题后面时不显示）
 * - 兼容旧写法：紧挨在 `{{toc}}` 前面的标题（手写的「## 目录」）不进目录
 * - 遍历整篇内容，写在哪里都能列全
 * - 目录项只给纯文本：标题里的术语取译名、格式去掉。目录项本身是链接，再套一层术语链接
 *   （悬停弹卡片、点击打开词条）会和跳转到标题抢鼠标
 *
 * tree：`{name: 'toc', props: {title}, children: [ul]}`；html：`<nav class="toc">`。
 */
final class TocTemplate extends Template
{
    use OwnsBlockTitle;

    protected string $component = 'toc';

    public function block(Call $call): bool
    {
        return true;
    }

    public function params(): array
    {
        return ['levels' => ParamSpec::at(1, '2-3'), 'title' => new ParamSpec];
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function levels(Call $call): array
    {
        if (preg_match('/^\s*([1-6])\s*(?:-\s*([1-6]))?\s*$/', (string) $call->param('levels'), $m) !== 1) {
            return [2, 3];
        }
        $from = (int) $m[1];
        $to = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : $from;

        return [min($from, $to), max($from, $to)];
    }

    private function title(Call $call, RenderContext $ctx): string
    {
        return $this->blockTitle($call, $ctx, 'labels.toc');
    }

    /**
     * 本篇内容里要进目录的标题（文档顺序）。
     *
     * @return list<array{level: int, id: string, nodes: list<Node>}>
     */
    private function headings(Call $call, RenderContext $ctx): array
    {
        if ($ctx->document === null) {
            return [];
        }
        [$from, $to] = $this->levels($call);
        $all = [];
        $skip = null;
        $this->walk($ctx->document->nodes, $call, $all, $skip);

        return array_values(array_filter(
            $all,
            fn (array $heading, int $index) => $index !== $skip && $heading['level'] >= $from && $heading['level'] <= $to,
            ARRAY_FILTER_USE_BOTH,
        ));
    }

    /**
     * @param  list<Node>  $nodes
     * @param  list<array{level: int, id: string, nodes: list<Node>}>  $all
     */
    private function walk(array $nodes, Call $self, array &$all, ?int &$skip): void
    {
        foreach ($nodes as $node) {
            if ($node === $self) {
                // 紧挨在目录前面的标题（手写的「## 目录」）不进目录
                $skip = $all === [] ? null : count($all) - 1;

                continue;
            }
            if (! $node instanceof Element) {
                continue;
            }
            if (preg_match('/^h([1-6])$/', $node->tag, $m) === 1 && isset($node->attrs['id'])) {
                $all[] = ['level' => (int) $m[1], 'id' => $node->attrs['id'], 'nodes' => $node->children];

                continue;
            }
            $this->walk($node->children, $self, $all, $skip);
        }
    }

    /**
     * 目录项文字：标题的纯文本（术语取译名，没有译名取原词；格式去掉）。
     *
     * @param  list<Node>  $nodes
     */
    private function label(array $nodes, Call $call, RenderContext $ctx): string
    {
        $text = new RenderContext(
            $ctx->options->with(['format' => Format::Text]),
            $ctx->resolver,
            new TextRenderer(Format::Text),
            $ctx->idPrefix,
        );

        return trim((string) preg_replace('/\s+/u', ' ', $text->renderString(new Document($nodes, true, '', [], $call->scope, fragment: true))));
    }

    /**
     * 按层级嵌套的 `ul > li > a`。
     *
     * @param  list<array{level: int, id: string, nodes: list<Node>}>  $headings
     */
    private function tree(array $headings, Call $call, RenderContext $ctx): ?Element
    {
        if ($headings === []) {
            return null;
        }
        $root = new Element('ul');
        /** @var list<array{level: int, list: Element}> $stack */
        $stack = [['level' => min(array_column($headings, 'level')), 'list' => $root]];
        foreach ($headings as $heading) {
            while ($heading['level'] < end($stack)['level'] && count($stack) > 1) {
                array_pop($stack);
            }
            while ($heading['level'] > end($stack)['level']) {
                $list = end($stack)['list'];
                $parent = end($list->children);
                if (! $parent instanceof Element) {
                    $parent = new Element('li');
                    $list->children[] = $parent;
                }
                $nested = new Element('ul');
                $parent->children[] = $nested;
                $stack[] = ['level' => end($stack)['level'] + 1, 'list' => $nested];
            }
            end($stack)['list']->children[] = new Element('li', [], [
                new Element('a', ['href' => '#'.$heading['id']], [new Text($this->label($heading['nodes'], $call, $ctx))]),
            ]);
        }

        return $root;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $title = $this->title($call, $ctx);

        return $title === '' ? [] : ['title' => $title];
    }

    public function children(Call $call, RenderContext $ctx): ?Document
    {
        $list = $this->tree($this->headings($call, $ctx), $call, $ctx);

        return $list === null ? null : new Document([$list], false, '', [], $call->scope);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $list = $this->tree($this->headings($call, $ctx), $call, $ctx);
        if ($list === null || ! $ctx->renderer instanceof HtmlRenderer) {
            return '';
        }
        $title = $this->title($call, $ctx);

        return '<nav class="toc">'
            .($title === '' ? '' : '<div class="toc-title">'.e($title).'</div>')
            .$ctx->renderer->nodes([$list], $ctx)
            .'</nav>';
    }

    /**
     * 纯文本：标题一行，下面按层级缩进列出。
     */
    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $this->lines($call, $ctx, fn (string $text, string $id) => $text);
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->plain($call, $ctx, Format::Text);
    }

    /**
     * markdown：`- [标题](#id)` 列表。
     */
    public function markdown(Call $call, RenderContext $ctx): string
    {
        return $this->lines($call, $ctx, fn (string $text, string $id) => "- [{$text}](#{$id})");
    }

    /**
     * @param  callable(string, string): string  $line
     */
    private function lines(Call $call, RenderContext $ctx, callable $line): string
    {
        $headings = $this->headings($call, $ctx);
        if ($headings === []) {
            return '';
        }
        $top = min(array_column($headings, 'level'));
        $title = $this->title($call, $ctx);
        $out = $title === '' ? [] : [$title];
        foreach ($headings as $heading) {
            $text = $this->label($heading['nodes'], $call, $ctx);
            $out[] = str_repeat('  ', $heading['level'] - $top).$line($text, $heading['id']);
        }

        return implode("\n", $out)."\n";
    }
}
