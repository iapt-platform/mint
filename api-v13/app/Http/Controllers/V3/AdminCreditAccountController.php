<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexAdminCreditAccountRequest;
use App\Http\Resources\V3\AdminCreditAccountResource;
use App\Services\V3\CreditAdminService;

/**
 * 管理后台：AI 对话积分账号一览（仅 root / administrator）。新资源，没有 v2 前身。
 */
class AdminCreditAccountController extends Controller
{
    public function __construct(private readonly CreditAdminService $credits) {}

    /**
     * 列出积分账号
     *
     * 有积分流水的账号（登录用户与游客设备），每行是该账号的汇总：积分余额、累计赚取、累计手工调整、
     * 累计消耗、今天用掉的赠送额度。最近有动静的在前。没有任何流水的用户不在列表里，
     * 但仍可以直接给他加积分（`POST /v3/admin/credit-adjustments`）。
     *
     * @queryParam filter[account_type] string 账号类型。Enum: user,guest
     * @queryParam filter[q] string 登录用户按用户名 / 昵称 / 邮箱模糊匹配，游客按设备 id 前缀匹配
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 100。Default: 20
     */
    public function index(IndexAdminCreditAccountRequest $request)
    {
        return AdminCreditAccountResource::collection(
            $this->credits->accounts($request->validated(), $request->integer('per_page', 20))
        );
    }
}
