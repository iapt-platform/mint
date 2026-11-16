<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Resources\V3\CreditPolicyResource;
use App\Services\V3\CreditService;

/**
 * AI 对话积分策略（单例资源，公开）。
 *
 * 新资源，没有 v2 前身。策略写在 `config('mint.credits.policy')`，只有当前生效的一版，历史看 git。
 */
class CreditPolicyController extends Controller
{
    public function __construct(private readonly CreditService $credits) {}

    /**
     * 查看当前的积分策略
     *
     * 每天赠送多少、每次提问多少钱、读经和编辑怎么换积分。`version` 是生效日（YYYYMMDD），
     * 也记在每笔积分流水里。「一天」按 UTC 算。
     *
     * @unauthenticated
     */
    public function __invoke()
    {
        return CreditPolicyResource::make($this->credits->policy());
    }
}
