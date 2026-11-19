<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\AdminCreditOverviewResource;
use App\Services\V3\CreditAdminService;

/**
 * 管理后台 AI 对话积分概览（单例资源，仅 root / administrator）。新资源，没有 v2 前身。
 */
class AdminCreditOverviewController extends Controller
{
    public function __construct(private readonly CreditAdminService $credits) {}

    /**
     * 积分概览
     *
     * 当前策略、全站合计（登录用户的积分余额总和、累计赚取、累计手工调整、今天的消耗）、
     * 最近 14 天每天结算出的积分，以及最近一次结算任务（每日任务或后台手工触发）的结果。
     */
    public function __invoke()
    {
        return AdminCreditOverviewResource::make($this->credits->overview());
    }
}
