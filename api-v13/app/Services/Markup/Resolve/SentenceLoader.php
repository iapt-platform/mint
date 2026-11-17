<?php

namespace App\Services\Markup\Resolve;

use App\Models\CustomBook;
use App\Models\Sentence;
use App\Services\ChannelService;
use App\Services\Markup\Ir\Document;
use App\Services\Markup\Ir\Scope;

/**
 * 句子模板的取数：按坐标取若干 channel 的句子，正文解析成下一层内容。
 *
 * channel 口径同 v2 CorpusController::getSentTpl()：调用方给的 channel，再加原文 channel——
 * 三藏（book < 1000）阅读模式是 `_System_Pali_VRI_`、编辑模式是 `_System_Wbw_VRI_`；自定义书是该书的 channel。
 * 所有句子一次查完（按段落分组 OR）。
 *
 * 只取渲染要用的字段。编辑器还要的 editor / studio / suggestionCount 等不在这里——
 * 那是编辑接口的契约，迁编辑模式时在 v3 另做。
 */
final class SentenceLoader extends Loader
{
    /** @var array<string, array{id: string, channels: list<string>, mode: string, depth: int}> */
    private array $pending = [];

    /** @var array<string, array<string, mixed>|null> */
    private array $results = [];

    public function __construct(private readonly ChannelService $channelService) {}

    /**
     * @param  list<string>  $channels
     */
    public function request(string $id, array $channels, string $mode, Scope $scope): string
    {
        // 层数进 key：句子正文里再引用同一个句子时，每层各有自己的结果，到最大层数为止（否则渲染时无限递归）
        $key = implode("\x1F", [$id, implode(',', $channels), $mode, $scope->depth]);
        if (! array_key_exists($key, $this->results)) {
            $this->pending[$key] = ['id' => $id, 'channels' => $channels, 'mode' => $mode, 'depth' => $scope->depth];
        }

        return $key;
    }

    /**
     * @return array{book: int, para: int, wordStart: int, wordEnd: int, origin: list<array<string, mixed>>, translation: list<array<string, mixed>>, commentaries: list<array<string, mixed>>}|null
     */
    public function result(string $key): ?array
    {
        return $this->results[$key] ?? null;
    }

    public function hasPending(): bool
    {
        return $this->pending !== [];
    }

    public function load(): void
    {
        $batch = $this->pending;
        $this->pending = [];

        $requests = [];
        foreach ($batch as $key => $request) {
            $parts = explode('-', $request['id']);
            if (count($parts) !== 4 || array_filter($parts, fn ($part) => ! ctype_digit(trim($part))) !== []) {
                $this->results[$key] = null;

                continue;
            }
            $requests[$key] = $request + ['coords' => array_map('intval', $parts)];
        }
        if ($requests === []) {
            return;
        }

        $customBooks = $this->customBookChannels($requests);
        $channelsByKey = [];
        $allChannels = [];
        $paragraphs = [];
        foreach ($requests as $key => $request) {
            [$book, $para] = $request['coords'];
            $origin = $book < 1000
                ? $this->channelService->systemUid($request['mode'] === 'read' ? '_System_Pali_VRI_' : '_System_Wbw_VRI_')
                : ($customBooks[$book] ?? false);
            $channels = $request['channels'];
            if ($origin !== false && $origin !== null) {
                $channels[] = (string) $origin;
            }
            $channels = array_values(array_unique($channels));
            $channelsByKey[$key] = $channels;
            array_push($allChannels, ...$channels);
            $paragraphs["{$book}-{$para}"] = [$book, $para];
        }
        $this->resolver->channels()->request($allChannels);

        $rows = Sentence::query()
            ->where(function ($query) use ($paragraphs) {
                foreach ($paragraphs as [$book, $para]) {
                    $query->orWhere(fn ($where) => $where->where('book_id', $book)->where('paragraph', $para));
                }
            })
            ->whereIn('channel_uid', array_values(array_unique($allChannels)))
            ->get(['uid', 'book_id', 'paragraph', 'word_start', 'word_end', 'channel_uid', 'content', 'content_type'])
            ->keyBy(fn (Sentence $row) => "{$row->book_id}-{$row->paragraph}-{$row->word_start}-{$row->word_end}@{$row->channel_uid}");

        foreach ($requests as $key => $request) {
            [$book, $para, $start, $end] = $request['coords'];
            $result = [
                'book' => $book, 'para' => $para, 'wordStart' => $start, 'wordEnd' => $end,
                'origin' => [], 'translation' => [], 'commentaries' => [],
            ];
            foreach ($channelsByKey[$key] as $channelUid) {
                $row = $rows->get("{$book}-{$para}-{$start}-{$end}@{$channelUid}");
                $channel = $this->resolver->channels()->get($channelUid);
                if ($row === null || $channel === null) {
                    continue;
                }
                $entry = [
                    'id' => (string) $row->uid,
                    'channel' => [
                        'id' => (string) $channel->uid,
                        'name' => (string) $channel->name,
                        'type' => (string) $channel->type,
                        'lang' => (string) $channel->lang,
                    ],
                    'contentType' => (string) $row->content_type,
                    'content' => (string) $row->content,
                    'body' => $this->body($row, (string) $channel->type, $request),
                ];
                $area = match ((string) $channel->type) {
                    'original', 'wbw' => 'origin',
                    'commentary' => 'commentaries',
                    default => 'translation',
                };
                $result[$area][] = $entry;
            }
            $this->results[$key] = $result;
        }
    }

    /**
     * 句子正文 → 下一层内容。逐词解析的 json（非 nissaya channel）不是标记文本，不解析。
     *
     * @param  array{channels: list<string>, depth: int}  $request
     */
    private function body(Sentence $row, string $channelType, array $request): ?Document
    {
        $parent = new Scope(channels: $request['channels'], depth: $request['depth']);
        if (! $parent->canNest() || ($row->content_type === 'json' && $channelType !== 'nissaya')) {
            return null;
        }
        // 义注 channel 的正文按全部 channel 渲染（v2 口径），其余按本句的 channel
        $channels = $channelType === 'commentary' ? $request['channels'] : [(string) $row->channel_uid];
        $document = $this->resolver->parser->parse(
            (string) $row->content,
            $parent->nested($channels, (string) $row->content_type, $channelType === 'nissaya' ? 'nissaya' : 'translation'),
        );
        $this->resolver->collect($document);

        return $document;
    }

    /**
     * 自定义书（book ≥ 1000）的原文 channel。
     *
     * @param  array<string, array{coords: list<int>}>  $requests
     * @return array<int, string>
     */
    private function customBookChannels(array $requests): array
    {
        $books = array_values(array_unique(array_filter(
            array_map(fn ($request) => $request['coords'][0], $requests),
            fn (int $book) => $book >= 1000,
        )));
        if ($books === []) {
            return [];
        }

        return CustomBook::whereIn('book_id', $books)->pluck('channel_id', 'book_id')
            ->map(fn ($uid) => (string) $uid)->all();
    }
}
