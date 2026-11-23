<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\Concerns\EmbedsArticle;

/**
 * 嵌入经文章节：`{{chapter|id=book-para|title|channel|style}}`，没给 id 时用 `book=` + `para=`。
 *
 * 与 `{{article|type=chapter|…}}` 等价（前端是同一个 `article` 组件），
 * 后者拼 id 也走 {@see self::resolveId()}。
 */
final class ChapterTemplate extends Template
{
    use EmbedsArticle;

    protected string $component = 'article';

    public function params(): array
    {
        return [
            'id' => ParamSpec::at(1),
            'title' => ParamSpec::at(2),
            'channel' => ParamSpec::at(3),
            'style' => ParamSpec::at(4),
            'book' => new ParamSpec,
            'para' => new ParamSpec,
        ];
    }

    /**
     * 章节 id（`book-para`）：给了 id 就用 id，否则由书号 + 段号拼出；书号也没给时为空。
     */
    public static function resolveId(string $id, string $book, string $para): string
    {
        if ($id !== '' || $book === '') {
            return $id;
        }

        return ((int) $book).'-'.((int) $para);
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return $this->embedProps($call, 'chapter', $this->id($call), ['style', 'channel', 'title']);
    }

    public function references(Call $call): array
    {
        return self::paragraphRef($this->id($call));
    }

    private function id(Call $call): string
    {
        return self::resolveId((string) $call->param('id'), (string) $call->param('book'), (string) $call->param('para'));
    }
}
