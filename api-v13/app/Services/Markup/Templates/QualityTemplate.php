<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 质量标签：`{{quality|featured}}`。只有 featured / standard / draft / pending 四个值，其余按 pending。
 */
final class QualityTemplate extends Template
{
    protected string $component = 'quality';

    private const VALUES = ['featured', 'standard', 'draft', 'pending'];

    public function params(): array
    {
        return ['value' => ParamSpec::at(1, 'pending')];
    }

    private function value(Call $call): string
    {
        $value = (string) $call->param('value');

        return in_array($value, self::VALUES, true) ? $value : 'pending';
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['value' => $this->value($call)];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->value($call);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $value = $this->value($call);

        return "<span class=\"tag-quality {$value}\">{$value}</span>";
    }
}
