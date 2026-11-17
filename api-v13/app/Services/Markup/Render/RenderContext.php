<?php

namespace App\Services\Markup\Render;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Scope;
use App\Services\Markup\RenderOptions;
use App\Services\Markup\Resolve\Loader;
use App\Services\Markup\Resolve\Resolver;

/**
 * 一段顶层内容渲染期间的状态：选项、取数结果、脚注编号与列表、引用到的经文段落、已出现过的术语。
 *
 * 取代 v2 的 `$GLOBALS['note_sn']` / `$GLOBALS['note']` / `$GLOBALS['ref_sent']` / `$GLOBALS['term-…']`。
 */
final class RenderContext
{
    public int $noteNumber = 0;

    /** @var list<array{sn: int, trigger: string, content: string}> 脚注（html / markdown 输出末尾的列表） */
    public array $footnotes = [];

    /** 正在渲染的顶层内容（ref 模板遍历它收集引用的经文段落） */
    public ?Document $document = null;

    /** @var array<string, int> 术语出现次数（markdown 输出只有第一次带巴利原词与脚注） */
    public array $termSeen = [];

    /**
     * @param  string  $idPrefix  这段内容生成的 HTML id 的前缀（边注的 checkbox / label）：同一页上多段内容
     *                            各自从 1 编号，没有前缀 id 会重复，点角标会切换到别处的边注
     */
    public function __construct(
        public readonly RenderOptions $options,
        public readonly Resolver $resolver,
        public readonly Renderer $renderer,
        public readonly string $idPrefix = '',
    ) {}

    /**
     * 本段内容里唯一、跨段也不重复的 HTML id。
     */
    public function htmlId(string $kind, int $number): string
    {
        return $this->idPrefix === '' ? "{$kind}-{$number}" : "{$kind}-{$this->idPrefix}-{$number}";
    }

    public function format(): Format
    {
        return $this->options->format;
    }

    /**
     * @template T of Loader
     *
     * @param  class-string<T>  $class
     * @return T
     */
    public function loader(string $class): Loader
    {
        return $this->resolver->loader($class);
    }

    /**
     * 按当前格式渲染一段内容（模板 props 里的 Document、slot、句子正文都走这里）。
     *
     * @return list<array<string, mixed>>|string
     */
    public function render(Document $document): array|string
    {
        return $this->renderer->document($document, $this);
    }

    /**
     * 按当前格式渲染，要求结果是字符串（html / 纯文本 / markdown 模板方法用）。
     */
    public function renderString(Document $document): string
    {
        $rendered = $this->render($document);

        return is_string($rendered) ? $rendered : '';
    }

    /**
     * 这段内容的语言：第一个 channel 的语言，没有 channel 用选项里的默认语言（v2 口径）。
     */
    public function lang(Scope $scope): string
    {
        $lang = $this->resolver->channels()->get($scope->firstChannel())?->lang;

        return $lang !== null && $lang !== '' ? (string) $lang : $this->options->lang;
    }

    public function nextNoteNumber(): int
    {
        return ++$this->noteNumber;
    }
}
