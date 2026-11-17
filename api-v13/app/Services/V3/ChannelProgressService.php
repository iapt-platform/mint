<?php

namespace App\Services\V3;

use App\Models\Channel;
use App\Models\CustomBook;
use App\Models\Sentence;
use App\Services\UserService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

/**
 * 一批句子在各 channel 的完成度（编辑器的「选版本」面板用）。
 *
 * 取代 v2 `ChannelController@progress`（`POST /v2/channel-progress`）。v2 那份逐个 channel 查句子（N+1）；
 * 这里固定 4 次查询：原文长度、有内容的 channel、channel 本身、各 channel 的已译句子。
 *
 * 列哪些 channel：这些句子上**有译文且看得见**的，加上**我能写的**（没译文也列，好让人挑一个开始写）。
 * 给了 `studio` 就只列该 studio（本人或团队）名下的——团队空间传团队 uuid。
 */
class ChannelProgressService
{
    /** 一次最多查多少句（一章通常几百句） */
    public const MAX_SENTENCES = 2000;

    /** 原文所在的系统 channel（巴利三藏）；自定义书（book ≥ 1000）的原文在 custom_books.channel_id */
    private const PALI_CHANNEL = '_System_Pali_VRI_';

    private const CUSTOM_BOOK_FROM = 1000;

    public function __construct(private readonly ChannelAbilityService $abilities) {}

    /**
     * @param  list<string>  $sentences  `book-para-wordStart-wordEnd`，格式不对的忽略
     * @return Collection<int, Channel> 每个 channel 挂好 abilities、final、content_created_at、content_updated_at
     *
     * @throws AuthorizationException 给了 studio，但不是本人也不是该团队成员
     */
    public function forSentences(array $sentences, ?string $viewerUid, ?string $studioUid): Collection
    {
        if ($studioUid !== null && ($viewerUid === null || ! $this->abilities->canListStudio($viewerUid, $studioUid))) {
            throw new AuthorizationException(__('messages.channel_studio_forbidden'));
        }

        $coords = $this->parse($sentences);
        if ($coords === []) {
            return collect();
        }
        $lengths = $this->originalLengths($coords);
        $keys = array_keys($coords);
        $tuples = array_values($coords);

        // 有内容的 channel（不分谁的，下面再套可见性）
        $withContent = Sentence::query()
            ->whereIns(['book_id', 'paragraph', 'word_start', 'word_end'], $tuples)
            ->where('strlen', '>', 0)
            ->distinct()
            ->pluck('channel_uid')
            ->all();

        $query = Channel::query()
            ->with([UserService::eagerLoadActor('owner')[0], 'ownerGroup:uid,name'])
            ->where(function ($q) use ($withContent, $viewerUid) {
                $q->where(function ($inner) use ($withContent, $viewerUid) {
                    $inner->whereIn('uid', $withContent);
                    $this->abilities->scopeListable($inner, $viewerUid);
                });
                if ($viewerUid !== null) {
                    $q->orWhere(fn ($inner) => $this->abilities->scopeWritable($inner, $viewerUid));
                }
            })
            ->when($studioUid, fn ($q, string $uid) => $q->where('owner_uid', $uid))
            ->orderByDesc('updated_at')
            ->orderBy('uid');
        $channels = $query->get();
        if ($channels->isEmpty()) {
            return $channels;
        }

        // 各 channel 在这批句子上的已译行，一次取完
        $done = [];
        Sentence::query()
            ->whereIns(['book_id', 'paragraph', 'word_start', 'word_end'], $tuples)
            ->whereIn('channel_uid', $channels->pluck('uid')->all())
            ->where('strlen', '>', 0)
            ->get(['channel_uid', 'book_id', 'paragraph', 'word_start', 'word_end', 'created_at', 'updated_at'])
            ->each(function (Sentence $row) use (&$done) {
                $done[$row->channel_uid][] = $row;
            });

        $abilities = $this->abilities->abilitiesMany($channels, $viewerUid);
        foreach ($channels as $channel) {
            $rows = $done[$channel->uid] ?? [];
            $translated = [];
            foreach ($rows as $row) {
                $translated["{$row->book_id}-{$row->paragraph}-{$row->word_start}-{$row->word_end}"] = true;
            }
            $channel->setAttribute('abilities', $abilities[$channel->uid]);
            $channel->setAttribute('final', $rows === [] ? null : array_map(
                fn (string $key) => [$lengths[$key] ?? 0, isset($translated[$key])],
                $keys,
            ));
            $channel->setAttribute('content_created_at', $rows === [] ? null : collect($rows)->min('created_at'));
            $channel->setAttribute('content_updated_at', $rows === [] ? null : collect($rows)->max('updated_at'));
        }

        return $channels;
    }

    /**
     * 句子 id → [book, para, start, end]，保持请求顺序、去重。
     *
     * @param  list<string>  $sentences
     * @return array<string, array{0: int, 1: int, 2: int, 3: int}>
     */
    private function parse(array $sentences): array
    {
        $coords = [];
        foreach ($sentences as $id) {
            $parts = explode('-', (string) $id);
            if (count($parts) !== 4 || array_filter($parts, fn ($p) => ! ctype_digit($p)) !== []) {
                continue;
            }
            $coords[implode('-', array_map('intval', $parts))] = array_map('intval', $parts);
        }

        return $coords;
    }

    /**
     * 每句原文的字符数（巴利三藏在系统原文 channel，自定义书在它自己的原文 channel）。
     *
     * @param  array<string, array{0: int, 1: int, 2: int, 3: int}>  $coords
     * @return array<string, int>
     */
    private function originalLengths(array $coords): array
    {
        $pali = Channel::where('name', self::PALI_CHANNEL)
            ->where('owner_uid', config('mint.admin.root_uuid'))
            ->value('uid');
        $customBooks = array_unique(array_filter(
            array_map(fn (array $c) => $c[0], $coords),
            fn (int $book) => $book >= self::CUSTOM_BOOK_FROM,
        ));
        $customChannels = $customBooks === []
            ? []
            : CustomBook::whereIn('book_id', $customBooks)->pluck('channel_id', 'book_id')->all();

        $tuples = [];
        foreach ($coords as $c) {
            $channel = $c[0] >= self::CUSTOM_BOOK_FROM ? ($customChannels[$c[0]] ?? $pali) : $pali;
            if ($channel) {
                $tuples[] = [...$c, $channel];
            }
        }
        if ($tuples === []) {
            return [];
        }

        $lengths = [];
        Sentence::query()
            ->whereIns(['book_id', 'paragraph', 'word_start', 'word_end', 'channel_uid'], $tuples)
            ->get(['book_id', 'paragraph', 'word_start', 'word_end', 'strlen'])
            ->each(function (Sentence $row) use (&$lengths) {
                $lengths["{$row->book_id}-{$row->paragraph}-{$row->word_start}-{$row->word_end}"] = (int) $row->strlen;
            });

        return $lengths;
    }
}
