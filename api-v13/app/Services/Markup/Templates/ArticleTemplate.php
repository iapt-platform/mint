<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;

/**
 * 嵌入文章 / 章节：`{{article|type|id|title|channel|style|book|paragraphs|anthology}}`。
 * `type=chapter` 且没给 id 时由 book + paragraphs 拼出 id。
 */
final class ArticleTemplate extends Template
{
    protected string $component = 'article';

    public function params(): array
    {
        return [
            'type' => ParamSpec::at(1),
            'id' => ParamSpec::at(2),
            'title' => ParamSpec::at(3),
            'channel' => ParamSpec::at(4),
            'style' => ParamSpec::at(5),
            'book' => ParamSpec::at(6),
            'paragraphs' => ParamSpec::at(7),
            'anthology' => ParamSpec::at(8),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $type = (string) $call->param('type');
        $id = (string) $call->param('id');
        $book = (string) $call->param('book');
        $paragraphs = (string) $call->param('paragraphs');
        if ($type === 'chapter' && $id === '') {
            $id = ((int) $book).'-'.((int) $paragraphs);
        }
        $props = ['type' => $type, 'id' => $id];
        // style 为空时不给：前端组件用它自己的默认值（modal），传空串反而什么都不渲染
        foreach (['style', 'channel', 'title', 'anthology'] as $name) {
            $value = (string) $call->param($name);
            if ($value !== '') {
                $props[$name] = $value;
            }
        }
        if ($book !== '') {
            $props['book'] = $book;
        }
        if ($paragraphs !== '') {
            $props['paragraphs'] = $paragraphs;
        }
        $props['parentChannels'] = $call->scope->channels;

        return $props;
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        return $format === Format::Prompt ? '' : (string) $call->param('title');
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        return '';
    }
}
