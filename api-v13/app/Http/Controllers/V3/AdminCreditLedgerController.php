<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexAdminCreditLedgerRequest;
use App\Http\Resources\V3\AdminCreditLedgerResource;
use App\Services\V3\CreditAdminService;

/**
 * 管理后台：AI 对话积分流水（仅 root / administrator）。新资源，没有 v2 前身。底层表 `credit_ledger`。
 */
class AdminCreditLedgerController extends Controller
{
    public function __construct(private readonly CreditAdminService $credits) {}

    /**
     * 列出积分流水
     *
     * 最新的在前。包括结算赚取（earn）、提问扣减（spend）、出错退回（refund）与手工调整（adjust）。
     *
     * @queryParam filter[account_type] string 账号类型；给 account_id 时必填。Enum: user,guest
     * @queryParam filter[account_id] string 账号 id（用户 uuid 或游客设备 id）
     * @queryParam filter[kind] string 流水类型。Enum: earn,spend,refund,adjust
     * @queryParam filter[bucket] string 池子：free 每日赠送 / earned 积分余额。Enum: free,earned
     * @queryParam filter[source] string 来源。Enum: reading,editing,agent_run,admin
     * @queryParam filter[from] string 起始日（UTC，Y-m-d，按流水归属日）
     * @queryParam filter[to] string 结束日（UTC，Y-m-d，含当天）
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 100。Default: 20
     */
    public function index(IndexAdminCreditLedgerRequest $request)
    {
        return AdminCreditLedgerResource::collection(
            $this->credits->ledger($request->validated(), $request->integer('per_page', 20))
        );
    }
}
