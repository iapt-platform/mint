<?php

namespace App\Services\V3;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 管理后台「阅读统计」。时间范围按 UTC 日，闭区间 [from, to]。
 *
 * 数据来源与口径（也写在 dashboard-v6 统计页每张卡片下方）：
 * - **活跃读者**：阅读记录 `recents` 在区间内有更新的去重登录用户。recents 是按（用户, 章节）upsert 的，
 *   只记最后一次，所以「打开章节」是「区间内有更新的阅读记录数」，不是逐次打开数。
 * - **客户端**：recents.param 里 `client=mobile` 或 `mode=reading`（mobile 的写法；网页写 read / edit / wbw）
 *   算 App，其余算 Web。
 * - **读经时长**：`reading_dailies`，只有 App 登录用户上报；Web 没有计时。
 * - **章节浏览**：`views` 在区间内有更新的行（访客 × 章节），含游客与搜索引擎爬虫，只做趋势参考。
 * - **查词**：`user_operation_logs` 里的 dict_lookup（v2 接口的登录用户请求）。
 *
 * 结果缓存 10 分钟。
 */
class ReadingStatsService
{
    private const CACHE_SECONDS = 600;

    public const PITAKA_TAGS = ['sutta', 'vinaya', 'abhidhamma', 'añña'];

    public const LAYER_TAGS = ['mūla', 'aṭṭhakathā', 'ṭīkā'];

    public function __construct(private readonly CreditService $credits) {}

    /**
     * @param  'all'|'web'|'mobile'  $client
     * @return array<string, mixed>
     */
    public function stats(CarbonImmutable $from, CarbonImmutable $to, string $client): array
    {
        $key = "admin:reading-stats:{$from->toDateString()}:{$to->toDateString()}:{$client}";

        return Cache::remember($key, self::CACHE_SECONDS, function () use ($from, $to, $client) {
            $days = $from->diffInDays($to) + 1;
            $prevTo = $from->subDay();
            $prevFrom = $prevTo->subDays($days - 1);

            return [
                'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'client' => $client],
                'summary' => $this->summary($from, $to, $client),
                'previous' => $this->summary($prevFrom, $prevTo, $client),
                'sticky' => $this->sticky($to, $client),
                'daily' => $this->daily($from, $to, $client),
                'top_chapters' => $this->topChapters($from, $to, $client),
                'pitaka_share' => $this->share($from, $to, $client, self::PITAKA_TAGS),
                'layer_share' => $this->share($from, $to, $client, self::LAYER_TAGS),
                'duration_buckets' => $client === 'web' ? [] : $this->durationBuckets($from, $to),
                'reading_credits' => $client === 'web' ? null : $this->readingCredits($from, $to),
            ];
        });
    }

    /**
     * @return array{active_readers: int, chapters_opened: int, reading_seconds: int|null, reading_users: int|null, page_views: int|null, dict_lookups: int}
     */
    private function summary(CarbonImmutable $from, CarbonImmutable $to, string $client): array
    {
        $recents = $this->recents($from, $to, $client);
        $reading = $client === 'web' ? null : DB::table('reading_dailies')
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('coalesce(sum(seconds), 0) as seconds, count(distinct user_uid) as users')->first();

        return [
            'active_readers' => (int) (clone $recents)->distinct()->count('user_uid'),
            'chapters_opened' => (int) (clone $recents)->count(),
            'reading_seconds' => $reading ? (int) $reading->seconds : null,
            'reading_users' => $reading ? (int) $reading->users : null,
            'page_views' => $client === 'mobile' ? null : (int) $this->between(DB::table('views'), 'updated_at', $from, $to)->count(),
            'dict_lookups' => (int) $this->between(DB::table('user_operation_logs'), 'created_at', $from, $to)
                ->where('op_type', 'dict_lookup')->count(),
        ];
    }

    /**
     * 日活 / 月活（以区间最后一天为准）。
     *
     * @return array{dau: int, mau: int}
     */
    private function sticky(CarbonImmutable $to, string $client): array
    {
        return [
            'dau' => (int) $this->recents($to, $to, $client)->distinct()->count('user_uid'),
            'mau' => (int) $this->recents($to->subDays(29), $to, $client)->distinct()->count('user_uid'),
        ];
    }

    /**
     * 每天一行：Web / App 活跃读者、读经分钟、章节浏览、查词。没有数据的日子补 0。
     *
     * @return list<array{date: string, readers_web: int, readers_mobile: int, reading_minutes: int, page_views: int, dict_lookups: int}>
     */
    private function daily(CarbonImmutable $from, CarbonImmutable $to, string $client): array
    {
        $readers = $this->recents($from, $to, $client)
            ->selectRaw('date(updated_at) as d, '.$this->clientExpr().' as c, count(distinct user_uid) as n')
            ->groupByRaw('1, 2')->get();
        $minutes = DB::table('reading_dailies')->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('day as d, sum(seconds) as s')->groupBy('day')->pluck('s', 'd');
        $views = $this->between(DB::table('views'), 'updated_at', $from, $to)
            ->selectRaw('date(updated_at) as d, count(*) as n')->groupByRaw('1')->pluck('n', 'd');
        $lookups = $this->between(DB::table('user_operation_logs'), 'created_at', $from, $to)->where('op_type', 'dict_lookup')
            ->selectRaw('date(created_at) as d, count(*) as n')->groupByRaw('1')->pluck('n', 'd');

        $rows = [];
        for ($day = $from; $day->lte($to); $day = $day->addDay()) {
            $d = $day->toDateString();
            $rows[$d] = [
                'date' => $d,
                'readers_web' => 0,
                'readers_mobile' => 0,
                'reading_minutes' => $client === 'web' ? 0 : intdiv((int) ($minutes[$d] ?? 0), 60),
                'page_views' => $client === 'mobile' ? 0 : (int) ($views[$d] ?? 0),
                'dict_lookups' => (int) ($lookups[$d] ?? 0),
            ];
        }
        foreach ($readers as $row) {
            $d = substr((string) $row->d, 0, 10);
            if (isset($rows[$d])) {
                $rows[$d]['readers_'.$row->c] = (int) $row->n;
            }
        }

        return array_values($rows);
    }

    /**
     * 热门章节：区间内阅读记录最多的章节（按去重读者数）。
     *
     * @return list<array{article_id: string, book: int, paragraph: int, title: string, collection: string|null, readers: int, records: int}>
     */
    private function topChapters(CarbonImmutable $from, CarbonImmutable $to, string $client, int $limit = 10): array
    {
        $top = $this->recents($from, $to, $client)
            ->where('type', 'chapter')
            ->selectRaw('article_id, count(distinct user_uid) as readers, count(*) as records')
            ->groupBy('article_id')->orderByDesc('readers')->orderByDesc('records')->limit($limit)->get();

        return $top->map(function ($row) {
            [$book, $para] = array_map('intval', explode('-', $row->article_id) + [0, 0]);
            $text = DB::table('pali_texts')->where('book', $book)->where('paragraph', $para)->first(['toc', 'path']);
            $path = $text ? json_decode((string) $text->path, true) : [];
            $title = $text && trim((string) $text->toc) !== '' ? trim((string) $text->toc) : (end($path)['title'] ?? $row->article_id);

            return [
                'article_id' => $row->article_id,
                'book' => $book,
                'paragraph' => $para,
                'title' => $title,
                'collection' => $path[0]['title'] ?? null,
                'readers' => (int) $row->readers,
                'records' => (int) $row->records,
            ];
        })->all();
    }

    /**
     * 阅读记录按书的分类标签（三藏 / 根本·义注·复注）分组计数。一本书属于哪一类是固定的，
     * 标签挂在 pali_texts 的章节行上（tag_maps.table_name = pali_texts），按书取第一个命中的标签。
     *
     * @param  list<string>  $tags
     * @return list<array{tag: string, records: int}>
     */
    private function share(CarbonImmutable $from, CarbonImmutable $to, string $client, array $tags): array
    {
        $bookTag = $this->bookTags($tags);
        $counts = [];
        $rows = $this->recents($from, $to, $client)->where('type', 'chapter')
            ->selectRaw("split_part(article_id, '-', 1) as book, count(*) as n")->groupByRaw('1')->get();
        foreach ($rows as $row) {
            $tag = $bookTag[(int) $row->book] ?? 'other';
            $counts[$tag] = ($counts[$tag] ?? 0) + (int) $row->n;
        }
        arsort($counts);

        return array_map(fn ($tag, $n) => ['tag' => $tag, 'records' => $n], array_keys($counts), $counts);
    }

    /**
     * 书 → 标签。目录结构很少变，缓存一天。
     *
     * @param  list<string>  $tags
     * @return array<int, string>
     */
    private function bookTags(array $tags): array
    {
        return Cache::remember('admin:book-tags:'.md5(implode(',', $tags)), 86400, fn () => DB::table('tag_maps as m')
            ->join('pali_texts as p', 'p.uid', '=', 'm.anchor_id')
            ->join('tags as t', 't.id', '=', 'm.tag_id')
            ->where('m.table_name', 'pali_texts')
            ->whereIn('t.name', $tags)
            ->selectRaw('p.book, min(t.name) as tag')
            ->groupBy('p.book')
            ->pluck('tag', 'book')
            ->mapWithKeys(fn ($tag, $book) => [(int) $book => (string) $tag])
            ->all());
    }

    /**
     * 单日读经时长分布（人·天）。
     *
     * @return list<array{bucket: string, user_days: int}>
     */
    private function durationBuckets(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = DB::table('reading_dailies')->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('count(*) filter (where seconds < 300) as b1')
            ->selectRaw('count(*) filter (where seconds >= 300 and seconds < 900) as b2')
            ->selectRaw('count(*) filter (where seconds >= 900 and seconds < 1800) as b3')
            ->selectRaw('count(*) filter (where seconds >= 1800 and seconds < 3600) as b4')
            ->selectRaw('count(*) filter (where seconds >= 3600) as b5')
            ->first();

        return [
            ['bucket' => '<5', 'user_days' => (int) $row->b1],
            ['bucket' => '5-15', 'user_days' => (int) $row->b2],
            ['bucket' => '15-30', 'user_days' => (int) $row->b3],
            ['bucket' => '30-60', 'user_days' => (int) $row->b4],
            ['bucket' => '>60', 'user_days' => (int) $row->b5],
        ];
    }

    /**
     * 读经积分发放：总额、获积分人数、达到每日上限的人·天、服务端按墙钟核减掉的比例。
     *
     * @return array{credits: int, users: int, capped_user_days: int, clamped_ratio: float}
     */
    private function readingCredits(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $cap = $this->credits->policy()['rules']['earn']['reading']['daily_cap'] ?? null;
        $earn = DB::table('credit_ledger')->where('account_type', 'user')->where('source', 'reading')->where('kind', 'earn')
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('coalesce(sum(delta), 0) as credits, count(distinct account_id) as users')
            ->when($cap !== null, fn ($q) => $q->selectRaw('count(*) filter (where delta >= ?) as capped', [(int) $cap]))
            ->when($cap === null, fn ($q) => $q->selectRaw('0 as capped'))
            ->first();
        $seconds = DB::table('reading_dailies')->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('coalesce(sum(seconds), 0) as kept, coalesce(sum(seconds_reported), 0) as reported')->first();

        return [
            'credits' => (int) $earn->credits,
            'users' => (int) $earn->users,
            'capped_user_days' => (int) $earn->capped,
            'clamped_ratio' => (int) $seconds->reported > 0
                ? round(1 - (int) $seconds->kept / (int) $seconds->reported, 4)
                : 0.0,
        ];
    }

    private function recents(CarbonImmutable $from, CarbonImmutable $to, string $client): Builder
    {
        $query = $this->between(DB::table('recents'), 'updated_at', $from, $to);
        if ($client !== 'all') {
            $query->whereRaw($this->clientExpr().' = ?', [$client]);
        }

        return $query;
    }

    private function clientExpr(): string
    {
        return "(case when param->>'client' = 'mobile' or param->>'mode' = 'reading' then 'mobile' else 'web' end)";
    }

    /**
     * 闭区间的 UTC 日 → 半开的时间区间 [from 00:00, to+1 00:00)。
     */
    private function between(Builder $query, string $column, CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return $query->where($column, '>=', $from->startOfDay())->where($column, '<', $to->addDay()->startOfDay());
    }
}
