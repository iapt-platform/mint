<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\ShowAgentQuotaRequest;
use App\Http\Resources\V3\AgentQuotaResource;
use App\Services\V3\AgentAccountService;
use App\Services\V3\CreditService;

/**
 * 当前账号的 AI 对话额度（单例资源）。web chat 与 mobile 显示「今日剩余 N 次 · 积分 X」用。
 *
 * 新资源，没有 v2 前身。
 */
class AgentQuotaController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly AgentAccountService $accounts,
        private readonly CreditService $credits,
    ) {}

    /**
     * 查看我的 AI 对话额度
     *
     * 可选登录：带 Bearer 看自己账号；游客带 `device_id`。登录用户读取时会顺带把今天（和昨天）
     * 的读经、编辑时长结算成积分，所以读完马上就能看到到账。
     *
     * @unauthenticated
     *
     * @queryParam device_id string 设备匿名 id（uuid）。未登录时必填
     */
    public function __invoke(ShowAgentQuotaRequest $request)
    {
        return AgentQuotaResource::make($this->credits->quota(
            $this->accounts->resolve($this->currentUserUid($request), $request->validated('device_id'))
        ));
    }
}
