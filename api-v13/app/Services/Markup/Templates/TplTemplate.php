<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\Resolver;
use App\Services\Markup\Resolve\TplLoader;

/**
 * 用户自定义模板：`{{Tpl:名字|a|b=c}}`。正文是标题为「Tpl:名字」的文章，参数经 Mustache 代入
 * （`{{1}}`、`{{b}}`），结果再按 markdown 渲染。
 */
final class TplTemplate extends Template
{
    protected string $component = 'tpl';

    protected function acceptsAnyName(): bool
    {
        return true;
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        $call->data['tpl'] = $resolver->loader(TplLoader::class)->request($call->name, $call->params->all(), $call->scope);
    }

    /**
     * @return array{content: string, document: ?Document}|null
     */
    private function resolved(Call $call, RenderContext $ctx): ?array
    {
        return $ctx->loader(TplLoader::class)->result((string) ($call->data['tpl'] ?? ''));
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['name' => $call->name];
    }

    public function children(Call $call, RenderContext $ctx): ?Document
    {
        return $this->resolved($call, $ctx)['document'] ?? null;
    }

    private function rendered(Call $call, RenderContext $ctx): string
    {
        $resolved = $this->resolved($call, $ctx);
        if ($resolved === null) {
            return '';
        }

        return $resolved['document'] === null ? $resolved['content'] : $ctx->renderString($resolved['document']);
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->rendered($call, $ctx);
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $this->rendered($call, $ctx);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $resolved = $this->resolved($call, $ctx);
        if ($resolved === null) {
            return '';
        }

        return $resolved['document'] === null ? e($resolved['content']) : $ctx->renderString($resolved['document']);
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        return $this->rendered($call, $ctx);
    }
}
