<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Render\RenderContext;

/**
 * 可展开的列表项：源码里 `- <div>…</div>` 形式的列表项由解析器自动生成，不是手写模板。
 * 前端把 children 渲染成可折叠的树（第一个子元素是标题）。
 */
final class ToggleTemplate extends Template
{
    protected string $component = 'toggle';

    public function children(Call $call, RenderContext $ctx): ?Document
    {
        return $call->slot('content');
    }

    private function content(Call $call, RenderContext $ctx): string
    {
        $content = $call->slot('content');

        return $content === null ? '' : $ctx->renderString($content);
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->content($call, $ctx);
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $this->content($call, $ctx);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return $this->content($call, $ctx);
    }
}
