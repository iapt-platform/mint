<?php

namespace App\Services\Markup;

use App\Services\Markup\Annotation\AnnotationSet;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Scope;
use App\Services\Markup\Parsing\MarkupParser;
use App\Services\Markup\Render\HtmlRenderer;
use App\Services\Markup\Render\MarkdownRenderer;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Render\Renderer;
use App\Services\Markup\Render\TextRenderer;
use App\Services\Markup\Render\TreeRenderer;
use App\Services\Markup\Resolve\Resolver;

/**
 * wikipali 正文（markdown + 类维基模板）的解析与渲染。取代 v2 的 `App\Http\Api\MdRender` + `TemplateRender`
 * （v2 继续用旧代码，不改）。
 *
 * 三段式：解析成 IR（{@see MarkupParser}，纯函数）→ 批量取数（{@see Resolver}）→ 按格式输出（{@see Renderer}）。
 * 一次渲染多段内容用 {@see renderMany()}，所有段落里的术语、句子合在一起查。
 *
 * ```php
 * $tree = $markup->render($sentence->content, new RenderOptions(
 *     format: Format::Tree,
 *     channels: [$sentence->channel_uid],
 *     contentType: $sentence->content_type,
 * ));
 * ```
 */
final class MarkupService
{
    public function __construct(private readonly MarkupParser $parser) {}

    /**
     * 只解析不取数（测试、静态分析用）。
     */
    public function parse(string $source, RenderOptions $options): Document
    {
        return $this->parser->parse($source, $this->scope($options), $options->paragraph);
    }

    /**
     * @return list<array<string, mixed>>|string tree 格式返回节点数组，其余返回字符串
     */
    public function render(?string $source, RenderOptions $options): array|string
    {
        return $this->renderMany(['content' => $source], $options)['content'];
    }

    /**
     * 批量渲染。每项可以是源码字符串，或带自己 channel / content_type 的数组（如一章里各句来自不同 channel）。
     *
     * @template K of array-key
     *
     * @param  array<K, string|null|array{source: ?string, channels?: list<string>, contentType?: string, channelType?: string, annotations?: AnnotationSet, idPrefix?: string}>  $items
     * @return array<K, list<array<string, mixed>>|string>
     */
    public function renderMany(array $items, RenderOptions $options): array
    {
        return $this->renderManyAs($items, $options, [$options->format])[$options->format->value];
    }

    /**
     * 同一批内容按多种格式输出（如 discussions 同时要节点树与纯文本摘要）：解析与取数只做一次。
     *
     * @template K of array-key
     *
     * @param  array<K, string|null|array{source: ?string, channels?: list<string>, contentType?: string, channelType?: string, annotations?: AnnotationSet, idPrefix?: string}>  $items
     * @param  list<Format>  $formats
     * @return array<string, array<K, list<array<string, mixed>>|string>> 格式名 => 各项结果
     */
    public function renderManyAs(array $items, RenderOptions $options, array $formats): array
    {
        $resolver = new Resolver($options, $this->parser);
        $documents = [];
        $idPrefixes = [];
        foreach ($items as $key => $item) {
            $itemOptions = is_array($item) ? $options->with(array_intersect_key($item, array_flip(['channels', 'contentType', 'channelType']))) : $options;
            $source = is_array($item) ? $item['source'] : $item;
            $annotations = is_array($item) ? ($item['annotations'] ?? null) : null;
            $idPrefixes[$key] = is_array($item) && isset($item['idPrefix']) ? (string) $item['idPrefix'] : null;
            $document = $this->parser->parse((string) $source, $this->scope($itemOptions), $options->paragraph, 0, $annotations);
            $resolver->collect($document);
            $documents[$key] = $document;
        }
        $resolver->run();

        $output = [];
        foreach ($formats as $format) {
            $output[$format->value] = $this->output($documents, $options->with(['format' => $format]), $resolver, $idPrefixes);
        }

        return $output;
    }

    /**
     * @template K of array-key
     *
     * @param  array<K, Document>  $documents
     * @param  array<K, string|null>  $idPrefixes  各项 HTML id 的前缀；没给的取内容的短哈希
     * @return array<K, list<array<string, mixed>>|string>
     */
    private function output(array $documents, RenderOptions $options, Resolver $resolver, array $idPrefixes): array
    {
        $renderer = $this->renderer($options->format);
        $output = [];
        foreach ($documents as $key => $document) {
            $idPrefix = $idPrefixes[$key] ?? substr(md5($key.'|'.$document->source), 0, 8);
            $ctx = new RenderContext($options, $resolver, $renderer, $idPrefix);
            $rendered = $renderer->document($document, $ctx);
            if (is_string($rendered) && $options->footnote) {
                $rendered .= match (true) {
                    $renderer instanceof HtmlRenderer => $renderer->footnotes($ctx),
                    $renderer instanceof MarkdownRenderer => $renderer->footnotes($ctx),
                    default => '',
                };
            }
            $output[$key] = $rendered;
        }

        return $output;
    }

    private function scope(RenderOptions $options): Scope
    {
        return new Scope(
            channels: array_values($options->channels),
            dialect: $options->dialect(),
            channelType: $options->channelType,
            contentType: $options->contentType,
        );
    }

    private function renderer(Format $format): Renderer
    {
        return match ($format) {
            Format::Tree => new TreeRenderer,
            Format::Html => new HtmlRenderer,
            Format::Markdown => new MarkdownRenderer,
            Format::Text, Format::Simple, Format::Prompt => new TextRenderer($format),
        };
    }
}
