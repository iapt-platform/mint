<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Tools\Tools;

/**
 * 缅文逐词释义：`{{nissaya|巴利|意思=意思2}}`。nissaya channel 的 `巴利=意思` 行解析时自动转成它。
 */
final class NissayaTemplate extends Template
{
    protected string $component = 'nissaya';

    public function params(): array
    {
        return ['pali' => ParamSpec::at(1), 'meaning' => ParamSpec::at(2)];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return [
            'pali' => (string) $call->param('pali'),
            'meaning' => explode('=', (string) $call->param('meaning')),
        ];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $call->param('pali').'၊'.$call->param('meaning');
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        if ($format !== Format::Prompt) {
            return $this->text($call, $ctx);
        }
        $meanings = explode('=', (string) $call->param('meaning'));

        return Tools::MyToRm((string) $call->param('pali')).':'.end($meanings);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $lang = $ctx->lang($call->scope);
        $pali = e((string) $call->param('pali'));
        $prefix = $lang === 'my' ? "<span lang=\"pi-Mymr\">{$pali}</span>၊ " : "<span lang=\"pi-Latn\">{$pali}</span> ";

        return $prefix.'<span lang="'.e($lang).'">'.e((string) $call->param('meaning')).'</span>';
    }
}
