<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Dialect;
use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Params;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\Resolver;

/**
 * 模板基类。一个模板一个子类，在 {@see TemplateRegistry} 登记名字。
 *
 * 生命周期：
 * 1. 解析：{@see Params()} 声明参数（位置、默认值、是否 markdown）；markdown 参数递归解析成 slot
 * 2. 取数：{@see collect()} 把要查的键交给 Resolver，同类模板一次批量查完（不再逐个查库）
 * 3. 输出：tree 用 {@see component()} + {@see props()} + {@see children()}；html / 纯文本 / markdown
 *    各有一个方法，默认都退回 {@see text()}，只有真的不同才覆盖
 *
 * 模板实例无状态（单例），每次调用的数据都在 {@see Call} 上。
 */
abstract class Template
{
    /** tree 输出的组件名（前端按它分发） */
    protected string $component = 'unknown';

    /**
     * @return array<string, ParamSpec>
     */
    public function params(): array
    {
        return [];
    }

    /**
     * 参数片段 → 参数表。
     *
     * `name=value` 当命名参数的条件：名字全是小写字母（v2 口径）；E2 方言下还要求名字是 {@see Params()}
     * 声明过的，否则整段按位置参数处理——LLM 写的 `x=y` 不会被误认成参数。
     *
     * @param  list<string>  $segments
     */
    public function parseParams(array $segments, Dialect $dialect): Params
    {
        $declared = $this->params();
        $named = [];
        $positional = [];
        foreach ($segments as $offset => $segment) {
            if (preg_match('/^([a-z]+)=(.*)$/s', $segment, $m) === 1
                && ($dialect === Dialect::Markdown || $this->acceptsAnyName() || isset($declared[$m[1]]))) {
                $named[$m[1]] = $m[2];
            } else {
                $positional[$offset + 1] = $segment;
            }
        }

        return new Params($named, $positional);
    }

    /**
     * 是否接受任意名字的命名参数（Tpl 模板把参数原样交给 Mustache）。
     */
    protected function acceptsAnyName(): bool
    {
        return false;
    }

    public function collect(Call $call, Resolver $resolver): void {}

    /**
     * html 输出是块级元素（目录的 nav、mermaid 的 pre）：段落里只有它时不包 `<p>`。
     */
    public function block(): bool
    {
        return false;
    }

    /**
     * 这次调用引用到的经文段落（`{{ref}}` 据此列参考文献）。只从参数里解析，不查库。
     *
     * @return list<array{0: int, 1: int}> [book, para]
     */
    public function references(Call $call): array
    {
        return [];
    }

    /**
     * `book-para` 或 `book-para-start-end` 形式的坐标 → [[book, para]]；不合法返回 []。
     *
     * @return list<array{0: int, 1: int}>
     */
    protected static function paragraphRef(string $id): array
    {
        $parts = explode('-', trim($id));
        if (count($parts) < 2 || ! ctype_digit($parts[0]) || ! ctype_digit($parts[1])) {
            return [];
        }

        return [[(int) $parts[0], (int) $parts[1]]];
    }

    public function component(Call $call, RenderContext $ctx): string
    {
        return $this->component;
    }

    /**
     * tree 输出的 props。值可以是 {@see Document}，由渲染器按当前格式展开。
     *
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return [];
    }

    /**
     * tree 输出的 children。
     */
    public function children(Call $call, RenderContext $ctx): ?Document
    {
        return null;
    }

    /**
     * 纯文本表示：text / simple / prompt / markdown / html 的默认输出都从它来。
     */
    public function text(Call $call, RenderContext $ctx): string
    {
        return '';
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $this->text($call, $ctx);
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return e($this->text($call, $ctx));
    }

    public function markdown(Call $call, RenderContext $ctx): string
    {
        return $this->text($call, $ctx);
    }
}
