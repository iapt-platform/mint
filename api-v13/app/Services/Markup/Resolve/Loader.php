<?php

namespace App\Services\Markup\Resolve;

/**
 * 批量取数器。模板在 collect 阶段登记键，{@see Resolver::run()} 一轮一轮地调 {@see load()}，
 * 直到所有 loader 都没有待办（loader 取回的内容里又有模板，会登记下一轮）。
 */
abstract class Loader
{
    protected Resolver $resolver;

    public function setResolver(Resolver $resolver): void
    {
        $this->resolver = $resolver;
    }

    abstract public function hasPending(): bool;

    abstract public function load(): void;
}
