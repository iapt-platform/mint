<?php

namespace App\Services\Markup\Templates\Concerns;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Element;
use App\Services\Markup\Ir\Node;
use App\Services\Markup\Ir\Text;
use App\Services\Markup\Render\RenderContext;

/**
 * 自带标题的块级模板（目录 {{toc}}、参考文献 {{ref}}）的标题规则：
 *
 * - 标题不是 `<h2>`，不算正文的一节；文字随内容语言（labels.*）
 * - `title=文字` 改文字，`title=`（空）不显示
 * - 紧挨在前面已经有手写标题（如「## 参考文献」+ `{{ref}}`）时不显示，免得出现两个标题；旧写法不用改
 */
trait OwnsBlockTitle
{
    /**
     * @param  string  $label  语言文件里的键，如 labels.toc
     */
    protected function blockTitle(Call $call, RenderContext $ctx, string $label): string
    {
        if (array_key_exists('title', $call->params->named)) {
            return trim($call->params->named['title']);
        }
        if ($this->precededByHeading($call, $ctx)) {
            return '';
        }

        // 语言目录与 channel 的语言代码同名（zh-Hans、en…），没有对应文件时回退 en
        return (string) __($label, [], $ctx->lang($call->scope));
    }

    /**
     * 模板是否紧跟在一个标题后面：它是所在段落的第一个内容，且这个段落（或它自己）的前一个块是标题。
     */
    protected function precededByHeading(Call $call, RenderContext $ctx): bool
    {
        return $ctx->document !== null && $this->findPreceded($ctx->document->nodes, $call) === true;
    }

    /**
     * @param  list<Node>  $nodes
     * @return bool|null null 表示这一层里没找到
     */
    private function findPreceded(array $nodes, Call $call): ?bool
    {
        $previous = null;
        foreach ($nodes as $node) {
            if ($node instanceof Text && trim($node->value) === '') {
                continue;
            }
            if ($node === $call) {
                return self::isHeading($previous);
            }
            if ($node instanceof Element) {
                if ($node->tag === 'p' && self::firstContent($node->children) === $call) {
                    return self::isHeading($previous);
                }
                $found = $this->findPreceded($node->children, $call);
                if ($found !== null) {
                    return $found;
                }
            }
            $previous = $node;
        }

        return null;
    }

    /**
     * @param  list<Node>  $nodes
     */
    private static function firstContent(array $nodes): ?Node
    {
        foreach ($nodes as $node) {
            if (! ($node instanceof Text && trim($node->value) === '')) {
                return $node;
            }
        }

        return null;
    }

    private static function isHeading(?Node $node): bool
    {
        return $node instanceof Element && preg_match('/^h[1-6]$/', $node->tag) === 1;
    }
}
