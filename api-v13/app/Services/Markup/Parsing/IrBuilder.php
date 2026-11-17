<?php

namespace App\Services\Markup\Parsing;

use App\Services\Markup\Annotation\AnnotationSet;
use App\Services\Markup\Annotation\AnnotationToken;
use App\Services\Markup\Annotation\AnnotationTokenNode;
use App\Services\Markup\Annotation\RangeWrapper;
use App\Services\Markup\Html\HtmlFragment;
use App\Services\Markup\Html\HtmlPolicy;
use App\Services\Markup\Html\InlineHtmlAssembler;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Scope;
use App\Services\Markup\Ir\Text;
use App\Services\Markup\Templates\NoteTemplate;
use Closure;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Emphasis;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Strong;
use League\CommonMark\Extension\Strikethrough\Strikethrough;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableCell;
use League\CommonMark\Extension\Table\TableRow;
use League\CommonMark\Extension\Table\TableSection;
use League\CommonMark\Extension\TaskList\TaskListItemMarker;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Inline\Newline;
use League\CommonMark\Node\Inline\Text as MdText;
use League\CommonMark\Node\Node as MdNode;

/**
 * CommonMark AST → IR。
 *
 * 除了逐个节点对译，还做四件事：
 * - 占位符换回模板调用；代码里的占位符换回源码原文
 * - ```mermaid 变成 mermaid 模板；`<li><div>…</div></li>` 变成 toggle 模板（v2 的「可展开」）
 * - 只有脚注的段落并到上一段末尾（```note 前后都是空行时）
 * - 原始 HTML 过白名单
 */
final class IrBuilder
{
    private int $headingCount = 0;

    /**
     * @param  array<int, Call>  $calls  占位符下标 => 调用
     * @param  array<int, string>  $raws  占位符下标 => 源码原文
     * @param  Closure(string, list<string>, string): Call  $makeCall  建内部生成的调用（mermaid、toggle）
     * @param  (Closure(int): ?Node)|null  $annotation  批注占位符下标 → 模板调用或区间边界（{@see AnnotationSet}）
     */
    public function __construct(
        private readonly array $calls,
        private readonly array $raws,
        private readonly Closure $makeCall,
        private readonly Scope $scope,
        private readonly string $headingSeed,
        private readonly ?Closure $annotation = null,
    ) {}

    /**
     * @return array{0: list<Node>, 1: bool} [节点, 是否单段落（行内）]
     */
    public function build(MdNode $document): array
    {
        $blocks = $this->blocks($document);
        if ($this->annotation !== null) {
            $blocks = RangeWrapper::apply($blocks, $this->scope);
        }
        if (count($blocks) === 1 && $blocks[0] instanceof Element && $blocks[0]->tag === 'p') {
            return [$blocks[0]->children, true];
        }

        return [$blocks, $blocks === []];
    }

    /**
     * @return list<Node>
     */
    private function blocks(MdNode $container, bool $tight = false): array
    {
        $out = [];
        foreach ($container->children() as $child) {
            if ($child instanceof Paragraph) {
                $inlines = $this->inlines($child);
                if ($tight) {
                    array_push($out, ...$inlines);

                    continue;
                }
                $previous = end($out);
                if ($this->onlyNotes($inlines) && $previous instanceof Element && $previous->tag === 'p') {
                    array_push($previous->children, ...$this->trimWhitespace($inlines));

                    continue;
                }
                if ($inlines !== []) {
                    $out[] = new Element('p', [], $inlines);
                }

                continue;
            }
            array_push($out, ...$this->block($child));
        }

        return $out;
    }

    /**
     * @return list<Node>
     */
    private function block(MdNode $node): array
    {
        return match (true) {
            $node instanceof Heading => [$this->heading($node)],
            $node instanceof BlockQuote => [new Element('blockquote', [], $this->blocks($node))],
            $node instanceof ListBlock => [$this->listBlock($node)],
            $node instanceof FencedCode => [$this->fencedCode($node)],
            $node instanceof IndentedCode => [$this->code($node->getLiteral(), '')],
            $node instanceof HtmlBlock => HtmlFragment::parseBlock($node->getLiteral(), $this->expandText(...)),
            $node instanceof ThematicBreak => [new Element('hr')],
            $node instanceof Table => [new Element('table', [], $this->tableChildren($node))],
            default => $this->blocks($node),
        };
    }

    private function heading(Heading $node): Element
    {
        $this->headingCount++;
        $id = 'h-'.substr(md5($this->headingSeed), 0, 8).'-'.$this->headingCount;

        return new Element('h'.$node->getLevel(), ['id' => $id], $this->inlines($node));
    }

    private function listBlock(ListBlock $node): Element
    {
        $data = $node->getListData();
        $ordered = $data->type === ListBlock::TYPE_ORDERED;
        $attrs = $ordered && $data->start !== null && $data->start !== 1 ? ['start' => (string) $data->start] : [];
        $items = [];
        foreach ($node->children() as $item) {
            if ($item instanceof ListItem) {
                $items[] = $this->listItem($this->blocks($item, $node->isTight()));
            }
        }

        return new Element($ordered ? 'ol' : 'ul', $attrs, $items);
    }

    /**
     * 列表项里只有一个 `<div>` 时变成 toggle（v2 react 格式的后处理，前端渲染成可展开的树）。
     *
     * @param  list<Node>  $children
     */
    private function listItem(array $children): Element
    {
        $significant = $this->trimWhitespace($children);
        if (count($significant) === 1 && $significant[0] instanceof Element && $significant[0]->tag === 'div') {
            $toggle = ($this->makeCall)('toggle', [], '');
            $toggle->slots['content'] = new Document($significant, false, '', [], $this->scope);

            return new Element('li', [], [$toggle]);
        }

        return new Element('li', [], $children);
    }

    private function fencedCode(FencedCode $node): Node
    {
        $words = $node->getInfoWords();
        $language = strtolower($words[0] ?? '');
        $literal = AnnotationToken::strip(Placeholder::restore($node->getLiteral(), $this->raws));
        if ($language === 'mermaid') {
            return ($this->makeCall)('mermaid', [rtrim($literal, "\n")], "```mermaid\n{$literal}```");
        }

        return $this->code($literal, $language);
    }

    private function code(string $literal, string $language): Element
    {
        $literal = AnnotationToken::strip(Placeholder::restore($literal, $this->raws));
        $attrs = $language !== '' ? ['class' => 'language-'.$language] : [];

        return new Element('pre', [], [new Element('code', $attrs, [new Text($literal)])]);
    }

    /**
     * @return list<Node>
     */
    private function tableChildren(Table $table): array
    {
        $sections = [];
        foreach ($table->children() as $section) {
            if (! $section instanceof TableSection) {
                continue;
            }
            $rows = [];
            foreach ($section->children() as $row) {
                if (! $row instanceof TableRow) {
                    continue;
                }
                $cells = [];
                foreach ($row->children() as $cell) {
                    if ($cell instanceof TableCell) {
                        $tag = $cell->getType() === TableCell::TYPE_HEADER ? 'th' : 'td';
                        $attrs = $cell->getAlign() !== null ? ['align' => $cell->getAlign()] : [];
                        $cells[] = new Element($tag, $attrs, $this->inlines($cell));
                    }
                }
                $rows[] = new Element('tr', [], $cells);
            }
            $sections[] = new Element($section->isHead() ? 'thead' : 'tbody', [], $rows);
        }

        return $sections;
    }

    /**
     * @return list<Node>
     */
    private function inlines(MdNode $container): array
    {
        $assembler = new InlineHtmlAssembler;
        foreach ($container->children() as $child) {
            if ($child instanceof HtmlInline) {
                $assembler->tag($child->getLiteral());

                continue;
            }
            foreach ($this->inline($child) as $node) {
                $assembler->add($node);
            }
        }

        return $assembler->nodes();
    }

    /**
     * @return list<Node>
     */
    private function inline(MdNode $node): array
    {
        return match (true) {
            $node instanceof PlaceholderNode => [$this->call($node->index)],
            $node instanceof AnnotationTokenNode => $this->annotationNode($node->index),
            $node instanceof MdText => [new Text($node->getLiteral())],
            $node instanceof Newline => $node->getType() === Newline::HARDBREAK ? [new Element('br')] : [new Text("\n")],
            $node instanceof Code => [new Element('code', [], [new Text(AnnotationToken::strip(Placeholder::restore($node->getLiteral(), $this->raws)))])],
            $node instanceof Emphasis => [new Element('em', [], $this->inlines($node))],
            $node instanceof Strong => [new Element('strong', [], $this->inlines($node))],
            $node instanceof Strikethrough => [new Element('del', [], $this->inlines($node))],
            $node instanceof Link => [$this->link($node)],
            $node instanceof Image => [$this->image($node)],
            $node instanceof TaskListItemMarker => [new Element('input', array_filter([
                'type' => 'checkbox',
                'disabled' => 'disabled',
                'checked' => $node->isChecked() ? 'checked' : null,
            ]))],
            default => $this->inlines($node),
        };
    }

    private function link(Link $node): Element
    {
        $attrs = [];
        $url = HtmlPolicy::url(AnnotationToken::strip(Placeholder::restore($node->getUrl(), $this->raws)));
        if ($url !== null) {
            $attrs['href'] = $url;
        }
        if (($node->getTitle() ?? '') !== '') {
            $attrs['title'] = (string) $node->getTitle();
        }

        return new Element('a', $attrs, $this->inlines($node));
    }

    private function image(Image $node): Element
    {
        $attrs = [];
        $url = HtmlPolicy::url(AnnotationToken::strip(Placeholder::restore($node->getUrl(), $this->raws)));
        if ($url !== null) {
            $attrs['src'] = $url;
        }
        $attrs['alt'] = $this->plainText($node);
        if (($node->getTitle() ?? '') !== '') {
            $attrs['title'] = (string) $node->getTitle();
        }

        return new Element('img', $attrs);
    }

    private function plainText(MdNode $node): string
    {
        $text = '';
        foreach ($node->iterator() as $child) {
            if ($child instanceof MdText || $child instanceof Code) {
                $text .= $child->getLiteral();
            }
        }

        return AnnotationToken::strip(Placeholder::restore($text, $this->raws));
    }

    /**
     * @return list<Node>
     */
    private function annotationNode(int $index): array
    {
        $node = $this->annotation === null ? null : ($this->annotation)($index);

        return $node === null ? [] : [$node];
    }

    private function call(int $index): Node
    {
        return $this->calls[$index] ?? new Text('');
    }

    /**
     * HTML 块里的文字：展开模板占位符（`<nt>{{65-464-2-27}}</nt>` 里的句子模板照常渲染）。
     *
     * @return list<Node>
     */
    private function expandText(string $text): array
    {
        $nodes = [];
        $parts = preg_split(Placeholder::PATTERN, $text, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
        foreach ($parts as $i => $part) {
            if ($i % 2 === 1) {
                $nodes[] = $this->call((int) $part);

                continue;
            }
            // 批注占位符
            $pieces = preg_split(AnnotationToken::PATTERN, $part, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];
            foreach ($pieces as $j => $piece) {
                if ($j % 2 === 1) {
                    array_push($nodes, ...$this->annotationNode((int) $piece));
                } elseif ($piece !== '') {
                    $nodes[] = new Text($piece);
                }
            }
        }

        return $nodes;
    }

    /**
     * @param  list<Node>  $nodes
     */
    private function onlyNotes(array $nodes): bool
    {
        $significant = $this->trimWhitespace($nodes);
        if ($significant === []) {
            return false;
        }
        foreach ($significant as $node) {
            if (! $node instanceof Call || ! $node->template instanceof NoteTemplate) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<Node>  $nodes
     * @return list<Node>
     */
    private function trimWhitespace(array $nodes): array
    {
        return array_values(array_filter(
            $nodes,
            fn (Node $node) => ! ($node instanceof Text && trim($node->value) === ''),
        ));
    }
}
