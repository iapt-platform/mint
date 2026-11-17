<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 图表：```mermaid 围栏（解析时转成本模板）。源码原样，不做 markdown 解析、不再 base64。
 *
 * html 输出 `<pre class="mermaid">`，由 library 页面按需加载 mermaid.js 渲染；没有 JS 时显示源码。
 */
final class MermaidTemplate extends Template
{
    protected string $component = 'mermaid';

    public function block(): bool
    {
        return true;
    }

    public function params(): array
    {
        return ['text' => ParamSpec::at(1)];
    }

    private function source(Call $call): string
    {
        return (string) $call->params->raw('text', 1);
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['text' => $this->source($call)];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return 'mermaid';
    }

    /**
     * prompt 给 LLM 原文：它读得懂 mermaid，只给「mermaid」一词会丢掉图里的信息。
     */
    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Prompt ? $this->markdown($call, $ctx) : 'mermaid';
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return '<pre class="mermaid">'.e($this->source($call)).'</pre>';
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        return "```mermaid\n".$this->source($call)."\n```";
    }
}
