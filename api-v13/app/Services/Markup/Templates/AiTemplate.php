<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Render\HtmlRenderer;
use App\Services\Markup\Render\RenderContext;

/**
 * AI 生成内容提示：`{{ai}}`、`{{ai|标题}}`、`{{ai|标题|说明}}`、`{{ai|title=…|message=…|style=tag}}`。
 *
 * - title：标题，缺省（或为空）是「AI 生成内容」（labels.ai_generated，随内容语言）
 * - message：说明文字，markdown（可嵌模板）
 * - style：`block`（默认）黄底引用块，左上角警告图标 + 标题，下面是说明；
 *   `tag` 行内只显示图标与「AI」，鼠标移入才显示标题与说明
 *
 * tree：`{name: 'ai', props: {title, style}, children: message}`。
 *
 * 取代旧的 AI 对话组件 `{{ai|model}}`（只有 v2 TemplateRender / dashboard-v4 在用，那边不动）。
 */
final class AiTemplate extends Template
{
    protected string $component = 'ai';

    private const STYLES = ['block', 'tag'];

    private const ICON = '<svg class="markup-ai-icon" viewBox="0 0 16 16" width="1em" height="1em" aria-hidden="true">'
        .'<path fill="currentColor" d="M8.87 1.5a1 1 0 0 0-1.74 0L.63 13a1 1 0 0 0 .87 1.5h13a1 1 0 0 0 .87-1.5L8.87 1.5ZM8 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 8 5Zm0 7.25a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"/>'
        .'</svg>';

    public function params(): array
    {
        return [
            'title' => ParamSpec::at(1),
            'message' => ParamSpec::markdown(2),
            'style' => ParamSpec::at(3, 'block'),
        ];
    }

    public function block(Call $call): bool
    {
        return $this->style($call) === 'block';
    }

    private function style(Call $call): string
    {
        $style = trim((string) $call->param('style'));

        return in_array($style, self::STYLES, true) ? $style : 'block';
    }

    private function title(Call $call, RenderContext $ctx): string
    {
        $title = trim((string) $call->param('title'));

        return $title !== '' ? $title : (string) __('labels.ai_generated', [], $ctx->lang($call->scope));
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['title' => $this->title($call, $ctx), 'style' => $this->style($call)];
    }

    public function children(Call $call, RenderContext $ctx): ?Document
    {
        return $call->slot('message');
    }

    private function message(Call $call, RenderContext $ctx): string
    {
        $slot = $call->slot('message');

        return $slot === null ? '' : trim($ctx->renderString($slot));
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        $message = $this->message($call, $ctx);
        $title = $this->title($call, $ctx);

        return $message === '' ? "[{$title}]" : "[{$title}] {$message}";
    }

    /**
     * 是界面提示而不是正文：prompt 里不出现。
     */
    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Prompt ? '' : $this->text($call, $ctx);
    }

    /**
     * 只用 span（block 样式靠 CSS 显示成块），写在句子中间、脚注里都不会切断外层结构。
     */
    public function html(Call $call, RenderContext $ctx): string
    {
        $slot = $call->slot('message');
        $message = $slot !== null && $ctx->renderer instanceof HtmlRenderer
            ? $ctx->renderer->inlineSafe($slot, $ctx)
            : e($this->message($call, $ctx));
        $title = e($this->title($call, $ctx));
        $messageHtml = $message === '' ? '' : '<span class="markup-ai-message">'.$message.'</span>';

        if ($this->style($call) === 'tag') {
            return '<span class="markup-ai markup-ai-tag" tabindex="0">'.self::ICON.'AI'
                .'<span class="markup-ai-popup" role="tooltip"><span class="markup-ai-title">'.$title.'</span>'.$messageHtml.'</span>'
                .'</span>';
        }

        return '<span class="markup-ai markup-ai-block" role="note">'
            .'<span class="markup-ai-title">'.self::ICON.$title.'</span>'.$messageHtml
            .'</span>';
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        $title = $this->title($call, $ctx);
        $message = $this->message($call, $ctx);
        if ($this->style($call) === 'tag') {
            return '⚠ AI';
        }

        return "\n\n> ⚠ **{$title}**".($message === '' ? '' : "\n>\n> ".str_replace("\n", "\n> ", $message))."\n\n";
    }
}
