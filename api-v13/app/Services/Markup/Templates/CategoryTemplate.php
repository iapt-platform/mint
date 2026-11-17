<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 分类标签：`{{category|name}}`。
 *
 * v2 把它的组件名误写成 para；这里用 category，前端补对应组件。
 */
final class CategoryTemplate extends Template
{
    protected string $component = 'category';

    public function params(): array
    {
        return ['name' => ParamSpec::at(1)];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['name' => (string) $call->param('name')];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return (string) $call->param('name');
    }

    /**
     * HTML 里带上类名与分类名，由页面决定样式与去向（百科页换成指向分类列表的标签链接，
     * 见 App\Services\WikiService::contentHtml() 与 resources/css/modules/wiki.css）。
     */
    public function html(Call $call, RenderContext $ctx): string
    {
        $name = e($this->text($call, $ctx));

        return '<span class="tpl-category" data-category="'.$name.'">'.$name.'</span>';
    }
}
