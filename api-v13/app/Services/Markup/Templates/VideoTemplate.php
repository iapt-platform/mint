<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Html\HtmlPolicy;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 视频：`{{v|url|style|title}}`。style 缺省 modal。地址只允许 http(s) 与站内路径。
 */
final class VideoTemplate extends Template
{
    protected string $component = 'video';

    public function params(): array
    {
        return ['url' => ParamSpec::at(1), 'style' => ParamSpec::at(2, 'modal'), 'title' => ParamSpec::at(3)];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return [
            'url' => HtmlPolicy::url((string) $call->param('url')) ?? '',
            'title' => (string) $call->param('title'),
            'style' => (string) $call->param('style'),
        ];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return (string) $call->param('title');
    }
}
