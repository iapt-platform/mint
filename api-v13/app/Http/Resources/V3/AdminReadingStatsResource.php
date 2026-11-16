<?php

namespace App\Http\Resources\V3;

use App\Services\V3\ReadingStatsService;
use Illuminate\Http\Request;

/**
 * 管理后台阅读统计（一次返回整页数据）。口径见 {@see ReadingStatsService}。
 *
 * client=web 时读经时长相关字段为 null / 空（Web 没有计时）；client=mobile 时章节浏览为 null。
 */
class AdminReadingStatsResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     range: array{from: string, to: string, client: string},
     *     summary: array{active_readers: int, chapters_opened: int, reading_seconds: int|null, reading_users: int|null, page_views: int|null, dict_lookups: int},
     *     previous: array{active_readers: int, chapters_opened: int, reading_seconds: int|null, reading_users: int|null, page_views: int|null, dict_lookups: int},
     *     sticky: array{dau: int, mau: int},
     *     daily: array<int, array{date: string, readers_web: int, readers_mobile: int, reading_minutes: int, page_views: int, dict_lookups: int}>,
     *     top_chapters: array<int, array{article_id: string, book: int, paragraph: int, title: string, collection: string|null, readers: int, records: int}>,
     *     pitaka_share: array<int, array{tag: string, records: int}>,
     *     layer_share: array<int, array{tag: string, records: int}>,
     *     duration_buckets: array<int, array{bucket: string, user_days: int}>,
     *     reading_credits: array{credits: int, users: int, capped_user_days: int, clamped_ratio: float}|null
     * }
     */
    public function toArray($request): array
    {
        return $this->resource;
    }
}
