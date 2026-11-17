<?php

namespace App\Services\Markup\Templates;

use App\Services\Markup\Format;
use App\Services\Markup\Ir\Call;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Render\RenderContext;
use App\Services\Markup\Resolve\Resolver;
use App\Services\Markup\Resolve\SentenceLoader;

/**
 * 句子：`{{sent|id=65-464-2-27}}`、简写 `{{65-464-2-27}}`、指定 channel `{{65-464-2-27@channel}}`。
 *
 * 参数：id、channel（逗号分隔，缺省用内容所属 channel）、text（both / origin / translation）、mode（read / edit）。
 * channel 与 text 共用第 2 个位置（v2 如此）。
 */
final class SentTemplate extends Template
{
    public function params(): array
    {
        return [
            'id' => ParamSpec::at(1),
            'channel' => ParamSpec::at(2),
            'text' => ParamSpec::at(2, 'both'),
            'mode' => ParamSpec::at(3),
        ];
    }

    /**
     * @return array{0: string, 1: list<string>} [句子坐标, channel 列表]
     */
    private function target(Call $call): array
    {
        $id = (string) $call->param('id');
        $channel = (string) $call->param('channel');
        $channels = $channel !== '' && ! in_array($channel, ['both', 'origin', 'translation'], true)
            ? array_values(array_filter(array_map('trim', explode(',', $channel))))
            : $call->scope->channels;
        if (str_contains($id, '@')) {
            [$id, $only] = explode('@', $id, 2);
            $channels = [trim($only)];
        }

        return [trim($id), $channels];
    }

    private function mode(Call $call, string $default): string
    {
        $mode = (string) $call->param('mode');

        return $mode !== '' ? $mode : $default;
    }

    private function show(Call $call): string
    {
        $show = (string) $call->param('text', 'both');

        return in_array($show, ['origin', 'translation'], true) ? $show : 'both';
    }

    public function collect(Call $call, Resolver $resolver): void
    {
        [$id, $channels] = $this->target($call);
        $call->data['sentence'] = $resolver->loader(SentenceLoader::class)
            ->request($id, $channels, $this->mode($call, $resolver->options->mode), $call->scope);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function sentence(Call $call, RenderContext $ctx): ?array
    {
        $sentence = $ctx->loader(SentenceLoader::class)->result((string) ($call->data['sentence'] ?? ''));
        if ($sentence !== null) {
            // 以调用为键：同一个调用在一次渲染里可能被取多次（props、纯文本…），只记一次
            $ctx->sentenceRefs[spl_object_id($call)] = [$sentence['book'], $sentence['para']];
        }

        return $sentence;
    }

    public function component(Call $call, RenderContext $ctx): string
    {
        return $this->mode($call, $ctx->options->mode) === 'read' ? 'sentread' : 'sentedit';
    }

    /**
     * `{id, show, origin, translation, commentaries?}`。id 是句子坐标 book-para-start-end；
     * 每条：`{id, channel{id,name,lang}, body}`，body 是渲染后的正文；编辑模式另给 content（源码）与 contentType。
     * 原文 / 译文 / 义注已经分组，所以不再给 channel.type；没有义注时不给 commentaries。
     *
     * @return array<string, mixed>
     */
    public function props(Call $call, RenderContext $ctx): array
    {
        [$id] = $this->target($call);
        $edit = $this->mode($call, $ctx->options->mode) !== 'read';
        $sentence = $this->sentence($call, $ctx);
        if ($sentence === null) {
            return ['id' => $id, 'show' => $this->show($call), 'error' => 'invalid sentence id'];
        }
        $props = ['id' => $id, 'show' => $this->show($call)];
        foreach (['origin', 'translation', 'commentaries'] as $area) {
            if ($area === 'commentaries' && $sentence[$area] === []) {
                continue;
            }
            $props[$area] = array_map(function (array $entry) use ($edit) {
                $item = [
                    'id' => $entry['id'],
                    'channel' => [
                        'id' => $entry['channel']['id'],
                        'name' => $entry['channel']['name'],
                        'lang' => $entry['channel']['lang'],
                    ],
                    'body' => $entry['body'],
                ];
                if ($edit) {
                    $item['content'] = $entry['content'];
                    $item['contentType'] = $entry['contentType'];
                }

                return $item;
            }, $sentence[$area]);
        }

        return $props;
    }

    /**
     * 各条正文按当前格式渲染后拼接。
     *
     * @param  list<array<string, mixed>>  $entries
     */
    private function join(array $entries, RenderContext $ctx, bool $trim = false, string $wrapClass = ''): string
    {
        $out = '';
        foreach ($entries as $entry) {
            $body = $entry['body'] ?? null;
            $text = $body instanceof Document ? $ctx->renderString($body) : '';
            if ($trim) {
                $text = trim($text);
            }
            $out .= $wrapClass === '' ? $text : '<span class="'.$wrapClass.'">'.$text.'</span>';
        }

        return $out;
    }

    public function text(Call $call, RenderContext $ctx): string
    {
        return $this->plain($call, $ctx, Format::Text);
    }

    public function plain(Call $call, RenderContext $ctx, Format $format): string
    {
        $sentence = $this->sentence($call, $ctx);
        if ($sentence === null) {
            return '';
        }
        $show = $this->show($call);
        $trim = $format === Format::Simple;
        $out = '';
        if ($show !== 'translation') {
            $out .= $this->join($sentence['origin'], $ctx, $trim);
        }
        if ($show !== 'origin') {
            $out .= $this->join($sentence['translation'], $ctx, $trim);
        }

        return $out;
    }

    public function html(Call $call, RenderContext $ctx): string
    {
        $sentence = $this->sentence($call, $ctx);
        if ($sentence === null) {
            return '';
        }
        $show = $this->show($call);
        $html = '<span class="sentence">';
        if ($show !== 'translation') {
            $html .= $this->join($sentence['origin'], $ctx, false, 'origin');
        }
        if ($show !== 'origin') {
            $html .= $this->join($sentence['translation'], $ctx, false, 'translation');
        }

        return $html.'</span>';
    }

    /**
     * 导出：按 origin / translation 选项取原文、译文；只要译文却没有译文时用原文代替（v2 口径）。
     */
    public function markdown(Call $call, RenderContext $ctx): string
    {
        $sentence = $this->sentence($call, $ctx);
        if ($sentence === null) {
            return '';
        }
        $show = $this->show($call);
        $out = '';
        if ($show !== 'translation' && $ctx->options->origin) {
            $out .= $this->join($sentence['origin'], $ctx, true);
        }
        if ($show !== 'origin' && $ctx->options->translation) {
            if ($sentence['translation'] !== []) {
                $out .= $this->join($sentence['translation'], $ctx, true);
            } elseif ($show === 'translation') {
                $out .= $this->join($sentence['origin'], $ctx, true);
            }
        }

        return $out;
    }
}
