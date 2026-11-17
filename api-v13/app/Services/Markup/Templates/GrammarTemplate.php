<?php

namespace App\Services\Markup\Templates;

use App\Services\ChannelService;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\Resolver;
use App\Services\Markup\Resolve\TermLoader;

/**
 * 语法术语查询：`{{g|word}}`，在系统语法术语 channel（`_System_Grammar_Term_{lang}_`，缺省 en）里查。
 */
final class GrammarTemplate extends Template
{
    protected string $component = 'grammar';

    public function __construct(private readonly ChannelService $channelService) {}

    public function params(): array
    {
        return ['word' => ParamSpec::at(1)];
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        $lang = $resolver->channels()->get($call->scope->firstChannel())?->lang ?: $resolver->options->lang;
        $channel = $this->channelService->systemUid(
            '_System_Grammar_Term_'.strtolower((string) $lang).'_',
            '_System_Grammar_Term_en_',
        );
        $call->data['term'] = $resolver->loader(TermLoader::class)->request(
            (string) $call->param('word'),
            '',
            $channel === false ? null : $channel,
            $call->scope,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        $word = (string) $call->param('word');

        return ['word' => $word, 'term' => TermTemplate::termProps($ctx, $call->data['term'] ?? null, $word)];
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return (string) $call->param('word');
    }
}
