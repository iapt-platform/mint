<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Templates\Concerns\CitesParagraph;
use App\Services\Markup\Templates\Concerns\EmbedsArticle;

/**
 * 嵌入文章 / 文集 / 章节 / 段落：`{{article|type|id|title|channel|style|book|paragraphs|anthology}}`。
 *
 * 章节另有独立的 {@see ChapterTemplate}（`{{chapter}}`）；这里的 `type=chapter` 是旧写法，
 * 没给 id 时由 book + paragraphs 拼出 id，拼法与它共用。
 */
final class ArticleTemplate extends Template
{
    use CitesParagraph;
    use EmbedsArticle;

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
        $id = $type === 'chapter' ? $this->paragraphId($call) : (string) $call->param('id');

        return $this->embedProps($call, $type, $id, ['style', 'channel', 'title', 'anthology', 'book', 'paragraphs']);
    }

    /**
     * 章节 / 段落（type=chapter|para）引用了经文段落；文章、文集不算。
     */
    public function references(Call $call): array
    {
        return $this->citesParagraph($call) ? self::paragraphRef($this->paragraphId($call)) : [];
    }

    /**
     * 章节 / 段落：`style=reference` 且没给标题时显示缩写 + 段号，与 {{chapter}}、{{para}} 相同。
     */
    protected function title(Call $call): string
    {
        return $this->citesParagraph($call)
            ? $this->citedTitle($call, $this->paragraphId($call))
            : (string) $call->param('title');
    }

    private function citesParagraph(Call $call): bool
    {
        return in_array((string) $call->param('type'), ['chapter', 'para'], true);
    }

    private function paragraphId(Call $call): string
    {
        return ChapterTemplate::resolveId(
            (string) $call->param('id'),
            (string) $call->param('book'),
            (string) $call->param('paragraphs'),
        );
    }
}
