<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\QuoteLoader;
use App\Services\Markup\Resolve\Resolver;

/**
 * 引用经文段落：`{{quote|book-para}}`，显示该段的目录标题。
 */
final class QuoteTemplate extends Template
{
    protected string $component = 'quote';

    public function params(): array
    {
        return ['para' => ParamSpec::at(1)];
    }

    public function references(Call $call): array
    {
        return self::paragraphRef((string) $call->param('para'));
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        $resolver->loader(QuoteLoader::class)->request((string) $call->param('para'));
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $paraId = (string) $call->param('para');
        $props = ['paraId' => $paraId];
        $found = $ctx->loader(QuoteLoader::class)->result($paraId);
        if ($found !== null && $found['toc'] !== '') {
            $props['pali'] = $found['toc'];
        }

        return $props;
    }

    /**
     * 段落的目录标题，没有就是 book-para。
     */
    public function text(Call $call, RenderContext $ctx): string
    {
        $props = $this->props($call, $ctx);

        return (string) ($props['pali'] ?? $props['paraId']);
    }
}
