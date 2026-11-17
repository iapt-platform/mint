<?php

namespace App\Services\Markup\Templates;

use App\Models\BookTitle;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\Concerns\OwnsBlockTitle;

/**
 * 参考文献：`{{ref}}`，列出本文引用到的巴利文献（按书名去重，按首次出现的顺序编号）。
 *
 * 引用来自整篇内容里各模板的 {@see Template::references()}：句子、para、quote、
 * 带 book + para 的 ql、章节 / 段落 article。遍历整篇内容而不是边渲染边记，所以 `{{ref}}` 写在哪里都能列全
 * （v2 只统计句子模板，且必须放在文末）。
 *
 * 标题：自带一行标题，规则见 {@see OwnsBlockTitle}（labels.references；`title=` 改文字或隐藏；
 * 紧跟在手写的「## 参考文献」后面时不显示）。没有任何引用时整块都不输出。
 *
 * 一段引用对应的书名：`book_titles` 里同一本书、起始段号 ≤ 该段的最近一条（一个 book 里可能收了几部作品；
 * v2 把排在前面的作品全列了进来，且用 < 漏掉了恰好引用作品首段的情况）。
 */
final class ReferenceTemplate extends Template
{
    use OwnsBlockTitle;

    protected string $component = 'reference';

    public function params(): array
    {
        return ['title' => new ParamSpec];
    }

    private function title(Call $call, RenderContext $ctx): string
    {
        return $this->blockTitle($call, $ctx, 'labels.references');
    }

    private const COPYRIGHT = 'CSCD V4 VRI 2008';

    /**
     * @return list<array{sn: int, title: string, copyright: string}>
     */
    private function bibliography(RenderContext $ctx): array
    {
        $refs = $ctx->document === null ? [] : $this->gather($ctx->document->nodes);
        if ($refs === []) {
            return [];
        }
        $books = array_values(array_unique(array_map(fn (array $ref) => $ref[0], $refs)));
        $titles = BookTitle::whereIn('book', $books)->orderByDesc('paragraph')->get(['book', 'paragraph', 'title']);

        $seen = [];
        $references = [];
        foreach ($refs as [$book, $para]) {
            $title = $titles->first(fn (BookTitle $row) => (int) $row->book === $book && (int) $row->paragraph <= $para);
            if ($title === null || isset($seen[$title->title])) {
                continue;
            }
            $seen[$title->title] = true;
            $references[] = ['sn' => count($references) + 1, 'title' => (string) $title->title, 'copyright' => self::COPYRIGHT];
        }

        return $references;
    }

    /**
     * 按文档顺序收集引用的 [book, para]（含模板参数里的内容，如脚注正文）。
     *
     * @param  list<Node>  $nodes
     * @return list<array{0: int, 1: int}>
     */
    private function gather(array $nodes): array
    {
        $refs = [];
        foreach ($nodes as $node) {
            if ($node instanceof Element) {
                array_push($refs, ...$this->gather($node->children));
            } elseif ($node instanceof Call) {
                array_push($refs, ...$node->template->references($node));
                foreach ($node->slots as $slot) {
                    array_push($refs, ...$this->gather($slot instanceof Document ? $slot->nodes : []));
                }
            }
        }

        return $refs;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $references = $this->bibliography($ctx);
        $title = $references === [] ? '' : $this->title($call, $ctx);

        return ($title === '' ? [] : ['title' => $title]) + ['pali' => $references];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        $references = $this->bibliography($ctx);
        if ($references === []) {
            return '';
        }
        $title = $this->title($call, $ctx);
        $text = $title === '' ? '' : $title."\n";
        foreach ($references as $reference) {
            $text .= '['.$reference['sn'].'] '.ucfirst($reference['title']).' '.$reference['copyright']."\n";
        }

        return $text;
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        $references = $this->bibliography($ctx);
        if ($references === []) {
            return '';
        }
        $title = $this->title($call, $ctx);
        $markdown = $title === '' ? '' : $title."\n\n";
        foreach ($references as $reference) {
            $markdown .= '['.$reference['sn'].'] **'.ucfirst($reference['title']).'** '.$reference['copyright']."\n\n";
        }

        return $markdown;
    }
}
