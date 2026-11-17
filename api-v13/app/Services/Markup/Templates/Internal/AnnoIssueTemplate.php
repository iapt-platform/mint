<?php

namespace App\Services\Markup\Templates\Internal;

use App\Services\HtmlAnnotationMarkup;
use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\ParamSpec;
use App\Services\Markup\Templates\Template;

/**
 * 阅读页的 issue 高亮：包住锚定的那段正文，点击看 issue。由批注注入生成，正文里写不出来。
 *
 * tree：`{name: 'anno-issue', props: {ids, severity}, children}`；html 与阅读器 CSS 约定的 span 一致；
 * 纯文本 / markdown 只输出被包住的正文。
 */
final class AnnoIssueTemplate extends Template
{
    protected string $component = 'anno-issue';

    public function params(): array
    {
        return ['ids' => new ParamSpec, 'severity' => new ParamSpec];
    }

    /**
     * @return list<string>
     */
    public static function ids(Call $call): array
    {
        return array_values(array_filter(explode(',', (string) $call->param('ids'))));
    }

    public static function severity(Call $call): ?string
    {
        $severity = (string) $call->param('severity');

        return $severity === '' ? null : $severity;
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['ids' => self::ids($call), 'severity' => self::severity($call)];
    }

    public function children(Call $call, RenderContext $ctx): ?Document
    {
        return $call->slot('content');
    }

    private function inner(Call $call, RenderContext $ctx): string
    {
        $content = $call->slot('content');

        return $content === null ? '' : $ctx->renderString($content);
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->inner($call, $ctx);
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $this->inner($call, $ctx);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return HtmlAnnotationMarkup::issueOpenTag(self::ids($call), self::severity($call)).$this->inner($call, $ctx).'</span>';
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        return $this->inner($call, $ctx);
    }
}
