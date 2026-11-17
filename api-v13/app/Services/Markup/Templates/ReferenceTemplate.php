<?php

namespace App\Services\Markup\Templates;

use App\Models\BookTitle;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 参考文献：`{{ref}}`，列出本文前面句子模板引用到的巴利文献（按书名去重）。要放在文末。
 */
final class ReferenceTemplate extends Template
{
    protected string $component = 'reference';

    private const COPYRIGHT = 'CSCD V4 VRI 2008';

    /**
     * @return list<array{sn: int, title: string, copyright: string}>
     */
    private function references(RenderContext $ctx): array
    {
        if ($ctx->sentenceRefs === []) {
            return [];
        }
        $books = BookTitle::orderByDesc('sn')->get(['book', 'paragraph', 'title']);
        $seen = [];
        $references = [];
        foreach ($ctx->sentenceRefs as [$book, $para]) {
            foreach ($books as $title) {
                if ((int) $title->book !== $book || (int) $title->paragraph >= $para || isset($seen[$title->title])) {
                    continue;
                }
                $seen[$title->title] = true;
                $references[] = ['sn' => count($references) + 1, 'title' => (string) $title->title, 'copyright' => self::COPYRIGHT];
            }
        }

        return $references;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['pali' => $this->references($ctx)];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        $text = '';
        foreach ($this->references($ctx) as $reference) {
            $text .= '['.$reference['sn'].'] '.ucfirst($reference['title']).' '.$reference['copyright']."\n";
        }

        return $text;
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        $markdown = '';
        foreach ($this->references($ctx) as $reference) {
            $markdown .= '['.$reference['sn'].'] **'.ucfirst($reference['title']).'** '.$reference['copyright']."\n\n";
        }

        return $markdown;
    }
}
