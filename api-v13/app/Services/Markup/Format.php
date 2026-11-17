<?php

namespace App\Services\Markup;

/**
 * 输出格式。
 *
 * - Tree：JSON 节点树，给 v3 API（dashboard-v6、wikipali-mobile）。节点形状见 {@see Render\TreeRenderer}
 * - Html：静态 HTML，给 Laravel library 阅读器与导出
 * - Text：纯文本；Simple：更省略的纯文本（标题等）；Prompt：喂给 LLM 的纯文本
 * - Markdown：模板展开后的 markdown，脚注转成 `[^n]`（导出用）
 *
 * v2 的 `react`（MdTpl + base64）、`unity`、`tex` 不在新服务里实现。
 */
enum Format: string
{
    case Tree = 'tree';
    case Html = 'html';
    case Text = 'text';
    case Simple = 'simple';
    case Prompt = 'prompt';
    case Markdown = 'markdown';

    public function isPlain(): bool
    {
        return in_array($this, [self::Text, self::Simple, self::Prompt], true);
    }
}
