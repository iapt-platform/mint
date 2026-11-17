<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\QaLoader;
use App\Services\Markup\Resolve\Resolver;

/**
 * 嵌入问答话题：`{{qa|discussion id|style}}`。
 */
final class QaTemplate extends Template
{
    protected string $component = 'qa';

    public function params(): array
    {
        return ['id' => ParamSpec::at(1), 'style' => ParamSpec::at(2)];
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        $resolver->loader(QaLoader::class)->request((string) $call->param('id'));
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $id = (string) $call->param('id');
        $props = ['id' => $id, 'title' => ''];
        $qa = $ctx->loader(QaLoader::class)->result($id);
        if ($qa !== null) {
            $props['title'] = (string) $qa->title;
            $props['resType'] = $qa->res_type;
        }

        return $props;
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return (string) $this->props($call, $ctx)['title'];
    }
}
