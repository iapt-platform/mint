<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreAdminCreditSettlementRequest;
use App\Http\Resources\V3\AdminCreditSettlementResource;
use App\Services\V3\CreditAdminService;

/**
 * 管理后台：手工跑一次积分结算（仅 root / administrator）。新资源，没有 v2 前身。
 *
 * 每日任务 `artisan credits:settle-activity` 出问题时用。两者同一套逻辑（CreditAdminService::settle）。
 */
class AdminCreditSettlementController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly CreditAdminService $credits) {}

    /**
     * 手工结算积分
     *
     * 把登录用户的读经、编辑时长结算成积分。可重复执行：同一用户同一天的积分是覆盖不是累加，不会重复入账。
     * 不给 `date`：从积分上线日到昨天，补齐所有漏结算的「用户 + 日」（与每日任务相同）；
     * 给 `date`：按当前策略重算那一天的所有用户。同步执行。
     *
     * @responseStatus 201 结算完成。响应体与 200 同形
     *
     * @bodyParam date string 只重算这一天（UTC，Y-m-d），不早于积分上线日、不晚于今天
     */
    public function store(StoreAdminCreditSettlementRequest $request)
    {
        $date = $request->validated('date');

        return AdminCreditSettlementResource::make($this->credits->settle(
            $date === null ? null : now('UTC')->parse($date),
            $this->currentUser($request)['user_uid'],
        ))->response()->setStatusCode(201);
    }
}
