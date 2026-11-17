<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\QuoteLinkLoader;
use App\Services\Markup\Resolve\Resolver;

/**
 * 引用某版本的册页：`{{ql|type|bookname|volume|page|style|title|book|para}}`，取数口径见 {@see QuoteLinkLoader}。
 */
final class QuoteLinkTemplate extends Template
{
    protected string $component = 'quote-link';

    public function params(): array
    {
        return [
            'type' => ParamSpec::at(1),
            'bookname' => ParamSpec::at(2),
            'volume' => ParamSpec::at(3),
            'page' => ParamSpec::at(4),
            'style' => ParamSpec::at(5, 'modal'),
            'title' => ParamSpec::at(6),
            'book' => ParamSpec::at(7),
            'para' => ParamSpec::at(8),
        ];
    }

    /**
     * 只有给了 book + para 的才算引用了经文段落（按书名册页引用的不知道是哪一段）。
     */
    public function references(Call $call): array
    {
        return self::paragraphRef($call->param('book').'-'.$call->param('para'));
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        $call->data['quoteLink'] = $resolver->loader(QuoteLinkLoader::class)->request($call);
    }

    /**
     * @return array{props: array<string, mixed>, text: string}
     */
    private function resolved(Call $call, RenderContext $ctx): array
    {
        $result = $ctx->loader(QuoteLinkLoader::class)->result((string) ($call->data['quoteLink'] ?? ''));
        if ($result === null) {
            return ['props' => ['found' => false], 'text' => ''];
        }
        $props = $result['props'];
        $text = '';
        if ($result['termKey'] !== null) {
            $term = TermTemplate::termProps($ctx, $result['termKey'], (string) $props['bookName']);
            $props['term'] = $term;
            if (isset($term['id'])) {
                $text = (string) $term['meaning'];
            } else {
                $text = (string) $call->param('bookname');
            }
        }

        return ['props' => $props, 'text' => $text.$result['text']];
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        return $this->resolved($call, $ctx)['props'];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->resolved($call, $ctx)['text'];
    }
}
