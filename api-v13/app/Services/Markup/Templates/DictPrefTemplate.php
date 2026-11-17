<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 词典前缀列表组件：`{{dict-pref|page|size}}`（缺省第 1 页、每页 100）。
 */
final class DictPrefTemplate extends Template
{
    protected string $component = 'dict-pref';

    public function params(): array
    {
        return ['page' => ParamSpec::at(1, '1'), 'size' => ParamSpec::at(2, '100')];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return ['currPage' => (int) $call->param('page'), 'pageSize' => (int) $call->param('size')];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return 'dict-pref';
    }
}
