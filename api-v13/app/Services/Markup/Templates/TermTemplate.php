<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Text;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\Resolver;
use App\Services\Markup\Resolve\TermLoader;

/**
 * 术语：`{{term|word}}`、`[[word]]`。
 *
 * `word@tag` 按 tag 查；`word#显示文字` 用指定文字代替译名。查询口径见 {@see TermLoader}。
 */
final class TermTemplate extends Template
{
    protected string $component = 'term';

    public function params(): array
    {
        return ['word' => ParamSpec::at(1)];
    }

    /**
     * @return array{word: string, tag: string, display: ?string}
     */
    private function word(Call $call): array
    {
        $word = (string) $call->param('word');
        $display = null;
        if (str_contains($word, '#')) {
            [$word, $display] = explode('#', $word, 2);
        }
        $tag = '';
        if (str_contains($word, '@')) {
            [$word, $tag] = explode('@', $word, 2);
        }

        return ['word' => $word, 'tag' => $tag, 'display' => $display];
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        ['word' => $word, 'tag' => $tag] = $this->word($call);
        $call->data['term'] = $resolver->loader(TermLoader::class)
            ->request($word, $tag, $call->scope->firstChannel(), $call->scope);
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        ['word' => $word, 'display' => $display] = $this->word($call);
        $props = self::termProps($ctx, $call->data['term'] ?? null, $word);
        if ($display !== null) {
            $props['meaning'] = $display;
        }

        return $props;
    }

    /**
     * 术语查询结果 → props（ql、g 模板也用）。字段沿用 v2 的 getTermProps()；note 是内容节点而不是 HTML。
     *
     * @return array<string, mixed>
     */
    public static function termProps(RenderContext $ctx, ?string $key, string $word): array
    {
        $result = $ctx->loader(TermLoader::class)->result((string) $key);
        $props = [
            'word' => $word,
            'parentChannelId' => $result['channel'],
            'parentStudioId' => $result['studio'],
        ];
        $term = $result['term'];
        if ($term !== null) {
            $props['id'] = (string) $term->guid;
            $props['meaning'] = (string) $term->meaning;
            $props['channel'] = $term->channal;
            if (! empty($term->other_meaning)) {
                $props['meaning2'] = (string) $term->other_meaning;
            }
            if ($result['community']) {
                $props['isCommunity'] = true;
            }
            // tree 输出不带 note：前端的术语组件打开弹窗时自己取词条详情，note 全文随正文下发是浪费
            // （一个词条的 note 动辄几十 KB）。markdown 导出用 note 做脚注（见 markdown()）
            if ($result['note'] !== null && $ctx->format() !== Format::Tree) {
                $props['note'] = $result['note'];
            }
        }

        return $props;
    }

    public function children(Call $call, RenderContext $ctx): Document
    {
        return new Document([new Text($this->label($call, $ctx))], true, '', [], $call->scope);
    }

    /**
     * 正文里显示的文字：找到术语时「译名(原词)」，否则原词。
     */
    private function label(Call $call, RenderContext $ctx): string
    {
        $props = $this->props($call, $ctx);

        return isset($props['meaning']) ? "{$props['meaning']}({$props['word']})" : $props['word'];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        $props = $this->props($call, $ctx);

        return $props['meaning'] ?? $props['word'];
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $props = $this->props($call, $ctx);
        $class = isset($props['id']) ? 'term-ref' : 'term-ref term_invalid';

        return '<span class="'.$class.'" data-id="'.e($props['id'] ?? '').'" data-term="'.e($props['word']).'">'
            .e($props['meaning'] ?? $props['word']).'</span>';
    }

    /**
     * 第一次出现写「译名(原词)」并把术语 note 作为脚注，之后只写译名（v2 口径）。
     */
    public function markdown(Call $call, RenderContext $ctx): string
    {
        $props = $this->props($call, $ctx);
        if (! isset($props['meaning'])) {
            return $props['word'];
        }
        $seen = $ctx->termSeen[$props['word']] ?? 0;
        $ctx->termSeen[$props['word']] = $seen + 1;
        if ($seen > 0) {
            return $props['meaning'];
        }
        $output = "{$props['meaning']}({$props['word']})";
        if (($props['note'] ?? null) instanceof Document) {
            $sn = $ctx->nextNoteNumber();
            $ctx->footnotes[] = ['sn' => $sn, 'trigger' => '', 'content' => $ctx->renderString($props['note'])];
            $output .= "[^{$sn}]";
        }

        return $output;
    }
}
