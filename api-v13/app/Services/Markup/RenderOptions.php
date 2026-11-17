<?php

namespace App\Services\Markup;

/**
 * 一次渲染的选项。字段与 v2 MdRender 的 $options 一一对应（去掉了 debug / studioId / unity）。
 */
final readonly class RenderOptions
{
    /**
     * @param  list<string>  $channels  内容所属的 channel uid。句子模板默认读这些 channel 的译文，
     *                                  术语按第一个 channel 查；第一个 channel 的语言也是默认语言
     * @param  string  $mode  read | edit：句子模板据此输出 sentread / sentedit
     * @param  string  $contentType  markdown | markdown-e2 | json | html（取自 content_type 列）
     * @param  string  $channelType  translation | nissaya：nissaya 的 `a=b` 行会转成 nissaya 模板
     * @param  bool  $footnote  html / markdown 输出是否在末尾附脚注列表
     * @param  bool  $paragraph  巴汉逐段对读：只由句子模板组成的段落复制成原文段 + 译文段
     * @param  bool  $origin  markdown 输出时句子模板是否带原文
     * @param  bool  $translation  markdown 输出时句子模板是否带译文
     */
    public function __construct(
        public Format $format = Format::Tree,
        public array $channels = [],
        public string $mode = 'read',
        public string $contentType = 'markdown',
        public string $channelType = 'translation',
        public string $lang = 'zh-Hans',
        public bool $footnote = false,
        public bool $paragraph = false,
        public bool $origin = true,
        public bool $translation = true,
    ) {}

    public function dialect(): Dialect
    {
        return Dialect::fromContentType($this->contentType);
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function with(array $changes): self
    {
        return new self(...array_merge(get_object_vars($this), $changes));
    }
}
