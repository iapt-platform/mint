<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 练习题：`{{exercise|id}}`。title 与 id 共用第 1 个位置（v2 如此），通常写成 `title=`。
 */
final class ExerciseTemplate extends Template
{
    protected string $component = 'exercise';

    public function params(): array
    {
        return ['id' => ParamSpec::at(1), 'title' => ParamSpec::at(1)];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return [
            'id' => (string) $call->param('id'),
            'title' => (string) $call->param('title'),
            'channel' => $call->scope->firstChannel(),
        ];
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Prompt ? '' : (string) $call->param('title');
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return '';
    }
}
