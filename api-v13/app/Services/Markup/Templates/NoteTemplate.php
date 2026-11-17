<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Dialect;
use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Params;
use App\Services\Markup\Render\HtmlRenderer;
use App\Services\Markup\Render\RenderContext;

/**
 * 脚注：`{{note|正文}}`、`{{n|正文}}`、```note 围栏；旧方言还有 `` `正文` `` 与单独一行的 ``` 围栏。
 *
 * 正文是 markdown（可换行、可分段、可嵌模板）。角标总在行内：写在哪里就在哪里。
 *
 * 参数：text（正文）、trigger（角标文字）、cite（来源说明）、citelink（book-para-start-end 跳转坐标）。
 * E2 方言只认第一个位置参数是正文，其余参数必须写名字；不是已声明参数名的片段连同 `|` 拼回正文，
 * 所以正文里出现 `|`、`a=b` 都不会被拆开。
 */
final class NoteTemplate extends Template
{
    protected string $component = 'note';

    public function params(): array
    {
        return [
            'text' => ParamSpec::markdown(1),
            'trigger' => ParamSpec::at(2),
            'cite' => ParamSpec::at(3),
            'citelink' => ParamSpec::at(4),
        ];
    }

    public function parseParams(array $segments, Dialect $dialect): Params
    {
        if ($dialect === Dialect::Markdown) {
            return parent::parseParams($segments, $dialect);
        }
        $named = [];
        $body = [];
        foreach ($segments as $segment) {
            if (preg_match('/^(text|trigger|cite|citelink)=(.*)$/s', $segment, $m) === 1) {
                $named[$m[1]] = $m[2];
            } else {
                $body[] = $segment;
            }
        }
        $positional = $body === [] ? [] : [1 => implode('|', $body)];

        return new Params($named, $positional);
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return array_filter([
            'trigger' => (string) $call->param('trigger'),
            'cite' => (string) $call->param('cite'),
            'citelink' => (string) $call->param('citelink'),
        ], fn (string $value) => $value !== '');
    }

    public function children(Call $call, RenderContext $ctx): ?Document
    {
        return $call->slot('text');
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return (string) $call->param('trigger');
    }

    /**
     * 纯文本里只留角标文字；simple 与 prompt 连角标也不要（v2 口径）。
     */
    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Text ? $this->text($call, $ctx) : '';
    }

    /**
     * 边注（sidenote）结构，与 library 阅读器的 CSS 配套；同时记进文末脚注列表。
     */
    public function html(Call $call, RenderContext $ctx): string
    {
        $sn = $ctx->nextNoteNumber();
        $trigger = (string) $call->param('trigger');
        // 角标嵌在句子中间：正文只能是行内元素，否则浏览器会切断 sidenote 的结构
        $slot = $call->slot('text');
        $content = $slot !== null && $ctx->renderer instanceof HtmlRenderer
            ? $ctx->renderer->inlineSafe($slot, $ctx)
            : $this->content($call, $ctx);
        $ctx->footnotes[] = ['sn' => $sn, 'trigger' => $trigger, 'content' => $content];

        $target = '';
        $parts = explode('-', (string) $call->param('citelink'));
        if (count($parts) === 4) {
            $target = ' data-book="'.e($parts[0]).'" data-para="'.e($parts[1]).'" data-start="'.e($parts[2]).'" data-end="'.e($parts[3]).'"';
        }
        $cite = (string) $call->param('cite');
        $citeHtml = $cite === '' ? '' : '<cite class="anno-jump"'.$target.'>'.e($cite).'</cite>';

        $id = $ctx->htmlId('sn', $sn);

        return '<label for="'.$id.'" class="margin-toggle sidenote-number"'.$target.'>'.e($trigger).'</label>'
            .'<input type="checkbox" id="'.$id.'" class="margin-toggle"/>'
            .'<span class="sidenote">'.$content.$citeHtml.'</span>';
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        $sn = $ctx->nextNoteNumber();
        $ctx->footnotes[] = [
            'sn' => $sn,
            'trigger' => (string) $call->param('trigger'),
            'content' => $this->content($call, $ctx),
        ];

        return "[^{$sn}]";
    }

    private function content(Call $call, RenderContext $ctx): string
    {
        $slot = $call->slot('text');

        return $slot === null ? '' : $ctx->renderString($slot);
    }
}
