<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ShowAdminStatsRequest;
use App\Http\Resources\V3\AdminReadingStatsResource;
use App\Services\V3\ReadingStatsService;
use App\Services\V3\StatsRange;

/**
 * 管理后台阅读统计（单例资源，仅 root / administrator）。新资源，没有 v2 前身。
 */
class AdminReadingStatsController extends Controller
{
    public function __construct(private readonly ReadingStatsService $stats) {}

    /**
     * 阅读统计
     *
     * 一次返回整页：指标（含上一同长区间作对比）、日活 / 月活、每日序列、热门章节、
     * 三藏与根本·义注·复注分布、读经时长分布、读经积分。结果缓存 10 分钟。
     * 口径：活跃读者来自阅读记录（recents，只有登录用户）；读经时长只有 App；
     * 章节浏览（views）含游客与搜索引擎爬虫。
     *
     * @queryParam from string 起始日（UTC，Y-m-d）。Default: 今天往前 29 天
     * @queryParam to string 结束日（UTC，Y-m-d，含当天）。Default: 今天
     * @queryParam client string 客户端。Enum: all,web,mobile。Default: all
     */
    public function __invoke(ShowAdminStatsRequest $request)
    {
        [$from, $to] = StatsRange::resolve($request->validated());

        return AdminReadingStatsResource::make($this->stats->stats($from, $to, $request->validated('client', 'all')));
    }
}
