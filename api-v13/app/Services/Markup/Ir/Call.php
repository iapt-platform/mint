<?php

namespace App\Services\Markup\Ir;

use App\Services\Markup\Resolve\Resolver;
use App\Services\Markup\Templates\Template;

/**
 * 一次模板调用：`{{name|a|b=c}}`、`[[term]]`、`` `note` ``、```note、```mermaid 都落成它。
 *
 * 参数原样保存；声明为 markdown 的参数（如脚注正文）在解析时就递归解析成 slot。
 * 取数阶段（{@see Resolver}）把查到的数据放进 $data。
 */
final class Call implements Node
{
    /** @var array<string, mixed> 取数结果，由模板自己约定键名 */
    public array $data = [];

    /**
     * @param  string  $name  源码里写的名字（别名也原样保留，如 `n`、`ql`）
     * @param  array<string, Document>  $slots  markdown 参数解析出的内容
     * @param  string  $raw  源码原文（`{{…}}` 整段），未知模板与超深嵌套时原样输出
     */
    public function __construct(
        public string $name,
        public Template $template,
        public Params $params,
        public array $slots,
        public Scope $scope,
        public string $raw,
    ) {}

    /**
     * 按「命名参数优先、其次位置参数、最后默认值」取参数，去首尾空白（v2 的 get_param）。
     */
    public function param(string $name, ?string $default = null): ?string
    {
        $spec = $this->template->params()[$name] ?? null;

        return $this->params->get($name, $spec?->position, $default ?? $spec?->default ?? '');
    }

    public function slot(string $name): ?Document
    {
        return $this->slots[$name] ?? null;
    }

    /**
     * 换掉一个命名参数，返回新的调用（逐段对读复制段落时用）。
     */
    public function withParam(string $name, string $value): self
    {
        $copy = clone $this;
        $copy->params = $this->params->with($name, $value);

        return $copy;
    }
}
