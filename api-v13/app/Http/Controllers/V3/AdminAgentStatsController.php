<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ShowAdminStatsRequest;
use App\Http\Resources\V3\AdminAgentStatsResource;
use App\Services\V3\AgentStatsService;
use App\Services\V3\StatsRange;

/**
 * 管理后台 AI 对话统计（单例资源，仅 root / administrator）。新资源，没有 v2 前身。
 */
class AdminAgentStatsController extends Controller
{
    public function __construct(private readonly AgentStatsService $stats) {}

    /**
     * AI 对话统计
     *
     * 一次返回整页：指标（含上一同长区间作对比）、每日按研究路径的提问数与耗时分位数、
     * 客户端分布、工具调用 Top、积分流水。结果缓存 10 分钟。
     *
     * @queryParam from string 起始日（UTC，Y-m-d）。Default: 今天往前 29 天
     * @queryParam to string 结束日（UTC，Y-m-d，含当天）。Default: 今天
     * @queryParam account_type string 账号类型。Enum: all,user,guest。Default: all
     */
    public function __invoke(ShowAdminStatsRequest $request)
    {
        [$from, $to] = StatsRange::resolve($request->validated());

        return AdminAgentStatsResource::make($this->stats->stats($from, $to, $request->validated('account_type', 'all')));
    }
}
