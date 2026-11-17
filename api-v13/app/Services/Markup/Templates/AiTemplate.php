<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * AI 对话组件：`{{ai|model}}`。prompt 输出里不出现。
 */
final class AiTemplate extends Template
{
    protected string $component = 'ai';

    public function params(): array
    {
        return ['model' => ParamSpec::at(1, '1')];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['model' => (string) $call->param('model')];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return 'ai';
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Prompt ? '' : 'ai';
    }
}
