<?php

namespace App\Services\V3;

use App\Facades\RedisCache;
use App\Models\GroupInfo;
use App\Models\GroupMember;
use App\Models\ProgressChapter;
use App\Models\Share;
use App\Models\View;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * 某一章有哪些 channel（译本 / 逐词 / 原文……）及各自的完成度。
 *
 * 取代 v2 `ProgressChapterController@index` 的 `view=chapter_channels` 分支。v2 那份
 * 原样保留、不共用（CLAUDE.md 铁律 2）。v3 端点用 {@see self::list()}，Laravel library 的
 * 作品版本页用 {@see self::all()}（经 `TipitakaCatalogService::workChannels()`）。与 v2 的差别：
 *
 * - **只列看得见的 channel**：公开的（status=30），加上登录用户自己的、共享给他（或他所在
 *   群组）的。v2 不判断可见性，私有 channel 的名字与进度对所有人可见。
 * - 不再返回 likes（前端没用）；views、studio 改成整页批量取，查询数与行数无关。
 */
class TipitakaChapterChannelService
{
    /** channels.status：全网公开 */
    private const STATUS_PUBLIC = 30;

    /** shares.res_type：channel */
    private const SHARE_CHANNEL = 2;

    public function __construct(private readonly UserService $users) {}

    /**
     * 列出一章的 channel，按完成度从高到低（分页，给 v3 端点）。
     *
     * 返回的模型上已挂好 `views`、`studio`、`progress_line` 三个属性，Resource 直接读，不再查库。
     *
     * @param  string|null  $viewerUid  当前登录用户；未登录只看得到公开 channel
     * @return LengthAwarePaginator<int, ProgressChapter>
     */
    public function list(int $book, int $para, ?string $viewerUid, int $perPage): LengthAwarePaginator
    {
        $page = $this->query($book, $para, $viewerUid)->paginate($perPage);
        $this->enrich($page->getCollection());

        return $page;
    }

    /**
     * 同 {@see self::list()}，但不分页、可排除一个 channel——给 Laravel library 的作品页用
     * （一章最多几十个 channel，整页渲染）。
     *
     * @param  string|null  $exceptChannel  不列的 channel（译本详情页的「其他版本」要排除当前译本）
     * @return Collection<int, ProgressChapter>
     */
    public function all(int $book, int $para, ?string $viewerUid, ?string $exceptChannel = null): Collection
    {
        $rows = $this->query($book, $para, $viewerUid)
            ->when($exceptChannel, fn (Builder $q) => $q->where('progress_chapters.channel_id', '<>', $exceptChannel))
            ->get();
        $this->enrich($rows);

        return $rows;
    }

    /**
     * 一章的进度行 join channel，已套可见性，按完成度降序。
     *
     * @return Builder<ProgressChapter>
     */
    private function query(int $book, int $para, ?string $viewerUid): Builder
    {
        return ProgressChapter::query()
            ->select([
                'progress_chapters.uid',
                'progress_chapters.book',
                'progress_chapters.para',
                'progress_chapters.channel_id',
                'progress_chapters.progress',
                'progress_chapters.updated_at',
                'channels.name as channel_name',
                'channels.type as channel_type',
                'channels.lang as channel_lang',
                'channels.owner_uid as channel_owner_uid',
            ])
            // inner join：channel 已删除的进度行没有意义，不列
            ->join('channels', 'progress_chapters.channel_id', '=', 'channels.uid')
            ->where('progress_chapters.book', $book)
            ->where('progress_chapters.para', $para)
            ->where(fn (Builder $q) => $this->readableBy($q, $viewerUid))
            ->orderByDesc('progress_chapters.progress')
            // 完成度相同时要稳定的 tiebreaker，否则跨页会漂移
            ->orderBy('progress_chapters.channel_id');
    }

    /**
     * 整批补上 views / studio / progress_line，查询数与行数无关。
     *
     * @param  Collection<int, ProgressChapter>  $rows
     */
    private function enrich(Collection $rows): void
    {
        $views = $this->viewCounts($rows->pluck('uid')->all());
        $studios = $this->studios($rows->pluck('channel_owner_uid')->all());

        foreach ($rows as $row) {
            $row->setAttribute('views', $views[$row->uid] ?? 0);
            $row->setAttribute('studio', $studios[$row->channel_owner_uid] ?? null);
            $row->setAttribute('progress_line', RedisCache::get(
                "/chapter_dynamic/{$row->book}/{$row->para}/ch_{$row->channel_id}"
            ));
        }
    }

    /**
     * 可见性：公开的，或（登录时）自己的、共享给自己或自己所在群组的。
     */
    private function readableBy(Builder $query, ?string $viewerUid): void
    {
        $query->where('channels.status', self::STATUS_PUBLIC);
        if ($viewerUid === null) {
            return;
        }

        $query->orWhere('channels.owner_uid', $viewerUid);

        $cooperators = GroupMember::where('user_id', $viewerUid)->pluck('group_id')->push($viewerUid);
        $shared = Share::whereIn('cooperator_id', $cooperators)
            ->where('res_type', self::SHARE_CHANNEL)
            ->pluck('res_id')
            // shares.res_id 是 varchar，channels.uid 是 uuid；非法值进了 where 会让 pgsql 报错
            ->filter(fn ($id) => Str::isUuid($id))
            ->unique()
            ->values();
        if ($shared->isNotEmpty()) {
            $query->orWhereIn('channels.uid', $shared);
        }
    }

    /**
     * 每个章节进度行被浏览的次数（views 表一行一次）。
     *
     * @param  list<string>  $uids  progress_chapters.uid
     * @return array<string, int>
     */
    private function viewCounts(array $uids): array
    {
        if (! $uids) {
            return [];
        }

        return View::whereIn('target_id', $uids)
            ->groupBy('target_id')
            ->selectRaw('target_id, count(*) as n')
            ->pluck('n', 'target_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * channel 所有者的摘要。所有者可能是用户，也可能是群组（v2 `StudioApi::getById` 的口径）。
     *
     * @param  list<string|null>  $ownerUids
     * @return array<string, array{id: string, nickName: string, realName: string, avatar?: string}>
     */
    private function studios(array $ownerUids): array
    {
        $output = [];
        foreach ($this->users->byUuids($ownerUids) as $uid => $profile) {
            $output[$uid] = array_filter([
                'id' => $uid,
                'nickName' => (string) ($profile['nickName'] ?? ''),
                'realName' => (string) ($profile['realName'] ?? ''),
                'avatar' => $profile['avatar'] ?? null,
            ], fn ($v) => $v !== null);
        }

        $groups = array_values(array_unique(array_diff(array_filter($ownerUids), array_keys($output))));
        if ($groups) {
            foreach (GroupInfo::whereIn('uid', $groups)->get(['uid', 'name']) as $group) {
                $output[$group->uid] = [
                    'id' => $group->uid,
                    'nickName' => (string) $group->name,
                    'realName' => (string) $group->uid,
                ];
            }
        }

        return $output;
    }
}
