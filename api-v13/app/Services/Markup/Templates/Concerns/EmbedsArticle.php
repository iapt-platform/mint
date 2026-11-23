<?php

namespace App\Services\Markup\Templates\Concerns;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 交给前端 `article` 组件嵌入的模板（{{article}}、{{chapter}}）共用的输出：
 * props 的拼法、纯文本只留标题、html 不输出（只有前端组件能渲染）。
 */
trait EmbedsArticle
{
    /**
     * @param  list<string>  $optional  有值才写进 props 的参数，按顺序
     * @return array<string, mixed>
     */
    protected function embedProps(Call $call, string $type, string $id, array $optional): array
    {
        $props = ['type' => $type, 'id' => $id];
        // style 为空时不给：前端组件用它自己的默认值（modal），传空串反而什么都不渲染
        foreach ($optional as $name) {
            $value = (string) $call->param($name);
            if ($value !== '') {
                $props[$name] = $value;
            }
        }
        $props['parentChannels'] = $call->scope->channels;

        return $props;
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Prompt ? '' : (string) $call->param('title');
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return '';
    }
}
