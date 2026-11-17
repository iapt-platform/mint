<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 没登记的模板名：tree 输出 `unknown` 组件（带名字），其余格式原样输出源码，不吞内容。
 */
final class UnknownTemplate extends Template
{
    protected string $component = 'unknown';

    protected function acceptsAnyName(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['name' => $call->name, 'source' => $call->raw];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $call->raw;
    }
}
