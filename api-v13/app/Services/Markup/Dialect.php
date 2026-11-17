<?php

namespace App\Services\Markup;

/**
 * 正文的标记方言，取自 sentences / articles / discussions 的 `content_type` 列。
 *
 * - Markdown（`markdown`）：旧数据。`` `x` `` 与单独一行的 ``` 围栏是脚注（不是代码）。
 * - MarkdownE2（`markdown-e2`）：标准 CommonMark + GFM，代码照常是代码；脚注只用 `{{note|…}}` / `{{n|…}}`
 *   或 ```note 围栏。
 *
 * 两种方言都认 `{{模板}}`、`[[术语]]`、`{{book-para-start-end}}` 句子简写、```note、```mermaid。
 */
enum Dialect: string
{
    case Markdown = 'markdown';
    case MarkdownE2 = 'markdown-e2';

    /**
     * 从 content_type 推方言。`markdown-e2` 以外（markdown / html / json / null）一律按旧方言处理，
     * 与 v2 的 MdRender 一致。
     */
    public static function fromContentType(?string $contentType): self
    {
        return $contentType === self::MarkdownE2->value ? self::MarkdownE2 : self::Markdown;
    }
}
