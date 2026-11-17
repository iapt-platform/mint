<?php

namespace App\Services\Markup\Templates\Internal;

use App\Services\HtmlAnnotationMarkup;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\ParamSpec;
use App\Services\Markup\Templates\Template;

/**
 * 阅读页句尾的三角叹号：没有位置的 issue 合成一个，点击看 issue。由批注注入生成。
 * 纯文本 / markdown 不输出。
 */
final class AnnoIssueMarkTemplate extends Template
{
    protected string $component = 'anno-issue-mark';

    public function params(): array
    {
        return ['ids' => new ParamSpec, 'severity' => new ParamSpec];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['ids' => AnnoIssueTemplate::ids($call), 'severity' => AnnoIssueTemplate::severity($call)];
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return (new HtmlAnnotationMarkup)->issueMark(AnnoIssueTemplate::ids($call), AnnoIssueTemplate::severity($call));
    }
}
