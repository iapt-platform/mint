<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreAdminCreditAdjustmentRequest;
use App\Http\Resources\V3\AdminCreditLedgerResource;
use App\Services\V3\CreditAdminService;

/**
 * 管理后台：手工加减 AI 对话积分（仅 root / administrator）。新资源，没有 v2 前身。
 * 与 `artisan credits:adjust` 同一套记账，后台这边另记操作人。
 */
class AdminCreditAdjustmentController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly CreditAdminService $credits) {}

    /**
     * 手工加减积分
     *
     * 加到登录用户的积分余额（不过期）。负数为扣减，扣减后余额不能为负。
     * 同一 `ref` 只入账一次：重复提交返回原来那条（200），新入账是 201。
     *
     * @bodyParam user string required 用户 uuid、用户名或邮箱
     * @bodyParam delta integer required 加减多少积分，非零，绝对值最大 1000000
     * @bodyParam note string required 原因（记进流水备注）
     * @bodyParam ref string 调整单号（uuid），防重复提交；不给由服务端生成
     *
     * @responseStatus 201 新入账。响应体与 200 同形（200 是同一 ref 已经入账过，返回原来那条）
     *
     * @meta created boolean 这次是否新入账
     * @meta earned_balance integer 该用户调整后的积分余额
     */
    public function store(StoreAdminCreditAdjustmentRequest $request)
    {
        $result = $this->credits->adjust(
            $this->currentUser($request)['user_uid'],
            $request->validated('user'),
            $request->integer('delta'),
            $request->validated('note'),
            $request->validated('ref'),
        );

        return AdminCreditLedgerResource::make($result['entry'])->additional(['meta' => [
            'created' => $result['created'],
            'earned_balance' => $result['earned_balance'],
        ]]);
    }
}
