<?php

namespace App\Services\V3;

use App\Models\Channel;
use App\Models\ProgressChapter;
use App\Models\Sentence;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * 阅读领域的 channel 查询：某段有内容的 channel、有译文的公开 channel 目录。
 *
 * - {@see self::forParagraph()} 取代 v2 `GET /v2/channel?view=paragraphs`（`ChannelController@index`）
 * - {@see self::withChapters()} 取代 v2 `GET /v2/progress?view=channel`（`ProgressChapterController@index`）
 *
 * v2 那两份原地保留不动（铁律 2）。可见性一律经 {@see ChannelAbilityService::scopeListable()}
 * （v2 的 paragraphs 不判断可见性，私有 channel 也列）。
 */
class TipitakaChannelService
{
    /** 「有译文」的完成度门槛，沿用 v2 `progress?view=channel` 的默认值 */
    public const MIN_PROGRESS = 0.8;

    public function __construct(private readonly ChannelAbilityService $abilities) {}

    /**
     * 某段有内容的 channel（看得见的），最近更新的在前。
     *
     * @return LengthAwarePaginator<int, Channel>
     */
    public function forParagraph(int $book, int $para, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $query = $this->base()->whereIn(
            'channels.uid',
            Sentence::query()->where('book_id', $book)->where('paragraph', $para)->select('channel_uid')
        );
        $this->abilities->scopeListable($query, $viewerUid);

        return $this->page($query->orderByDesc('channels.updated_at')->orderBy('channels.uid'), $viewerUid, $perPage);
    }

    /**
     * 有已译章节的公开 channel，按章节数降序。`lang` 按 progress_chapters.lang（本来就是语族粒度，
     * zh 涵盖简繁）。每条挂 `chapter_count`。
     *
     * @param  array{type?: string, lang?: string, min_progress?: float}  $filters
     * @return LengthAwarePaginator<int, Channel>
     */
    public function withChapters(array $filters, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $counts = ProgressChapter::query()
            ->selectRaw('channel_id, count(*) as chapter_count')
            ->where('progress', '>', $filters['min_progress'] ?? self::MIN_PROGRESS)
            ->when($filters['lang'] ?? null, fn (Builder $q, string $v) => $q->where('lang', $v))
            ->groupBy('channel_id');

        $query = $this->base()
            ->addSelect('chapters.chapter_count')
            ->joinSub($counts, 'chapters', 'chapters.channel_id', '=', 'channels.uid')
            ->where('channels.status', '>=', Channel::STATUS_PUBLIC)
            ->when($filters['type'] ?? null, fn (Builder $q, string $v) => $q->where('channels.type', $v))
            ->orderByDesc('chapters.chapter_count')
            ->orderBy('channels.uid');

        return $this->page($query, $viewerUid, $perPage);
    }

    /**
     * @return Builder<Channel>
     */
    private function base(): Builder
    {
        return Channel::query()
            ->select([
                'channels.uid', 'channels.name', 'channels.summary', 'channels.type', 'channels.lang',
                'channels.status', 'channels.is_system', 'channels.owner_uid', 'channels.source_type',
                'channels.source_id', 'channels.created_at', 'channels.updated_at',
            ])
            ->with([UserService::eagerLoadActor('owner')[0], 'ownerGroup:uid,name']);
    }

    /**
     * @param  Builder<Channel>  $query
     * @return LengthAwarePaginator<int, Channel>
     */
    private function page(Builder $query, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $page = $query->paginate($perPage);
        $abilities = $this->abilities->abilitiesMany($page->getCollection(), $viewerUid);
        foreach ($page->getCollection() as $channel) {
            $channel->setAttribute('abilities', $abilities[$channel->uid]);
        }

        return $page;
    }
}
