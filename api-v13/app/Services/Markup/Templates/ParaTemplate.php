<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\Concerns\CitesParagraph;

/**
 * 嵌入经文段落：`{{para|id=book-para|title|style}}`。
 * `style=reference` 且没给标题时显示缩写 + 段号（{@see CitesParagraph}）。
 */
final class ParaTemplate extends Template
{
    use CitesParagraph;

    protected string $component = 'para';

    public function params(): array
    {
        return ['id' => ParamSpec::at(1), 'title' => ParamSpec::at(2), 'style' => ParamSpec::at(3)];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $props = [
            'id' => (string) $call->param('id'),
            'title' => $this->citedTitle($call, (string) $call->param('id')),
            'style' => (string) $call->param('style'),
        ];
        if ($call->scope->channels !== []) {
            $props['channel'] = implode('_', $call->scope->channels);
        }

        return $props;
    }

    public function references(Call $call): array
    {
        return self::paragraphRef((string) $call->param('id'));
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->citedTitle($call, (string) $call->param('id'));
    }
}
