<?php

namespace App\Services\Markup\Ir;

use App\Services\Markup\Dialect;

/**
 * 一段内容的上下文：属于哪些 channel、用哪种方言、在第几层内容里。
 *
 * 句子模板取回的译文、术语的 note、Tpl 模板的正文都是「另一段内容」，各自带新的 Scope（depth + 1）。
 * 模板参数里的 markdown（如脚注正文）是同一段内容的一部分，沿用外层 Scope。
 */
final readonly class Scope
{
    /** 内容嵌套的最大层数：文章 → 句子译文 → 术语 note。再往下按纯文本输出（v2 是两层） */
    public const MAX_DEPTH = 3;

    /**
     * @param  list<string>  $channels
     * @param  bool  $inTermNote  这段内容是某个术语的 note：里面的术语不再取它们自己的 note（只展开一层，v2 口径）
     */
    public function __construct(
        public array $channels = [],
        public Dialect $dialect = Dialect::Markdown,
        public int $depth = 1,
        public string $channelType = 'translation',
        public string $contentType = 'markdown',
        public bool $inTermNote = false,
    ) {}

    public function firstChannel(): ?string
    {
        return $this->channels[0] ?? null;
    }

    /**
     * 下一层内容的 Scope。
     *
     * @param  list<string>|null  $channels  null 表示沿用当前 channel
     */
    public function nested(?array $channels = null, ?string $contentType = null, string $channelType = 'translation'): self
    {
        $contentType ??= 'markdown';

        return new self(
            channels: $channels ?? $this->channels,
            dialect: Dialect::fromContentType($contentType),
            depth: $this->depth + 1,
            channelType: $channelType,
            contentType: $contentType,
        );
    }

    public function canNest(): bool
    {
        return $this->depth < self::MAX_DEPTH;
    }
}
