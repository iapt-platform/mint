<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreAgentChargeRequest;
use App\Http\Resources\V3\AgentChargeResource;
use App\Services\V3\AgentAccountService;
use App\Services\V3\CreditService;

/**
 * AI 对话的提问扣费。只给 agent 服务调（`auth.agent-service`），不对客户端开放。
 *
 * 新资源，没有 v2 前身。
 */
class AgentChargeController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(
        private readonly AgentAccountService $accounts,
        private readonly CreditService $credits,
    ) {}

    /**
     * 为一次提问扣费（幂等）
     *
     * 一次提问开始前由 agent 服务调用。先扣今日赠送，不够再扣积分余额；合计不够返回 402
     * （`type` 为 quota-exceeded，扩展字段 `reason`：insufficient 积分不足 / ip_cap 游客同 IP 超上限，
     * 另带 `cost`、`free_remaining`、`earned_balance`）。
     *
     * 账号口径与 `POST /v3/agent-traces` 相同：Bearer 是透传的终端用户 token，
     * 无效或缺省按游客算，此时 `device_id` 必填。按 run_id 幂等：首次 201，重复 200。
     * 服务端出错、回答没生成时，写研究过程那一步会自动退回。
     *
     * @responseStatus 201 首次扣费。响应体与 200 同形
     * @responseStatus 402 额度不足或游客同 IP 超上限
     * @responseStatus 403 thread_id 已经属于别的账号
     *
     * @bodyParam run_id string required AG-UI runId（uuid）
     * @bodyParam thread_id string AG-UI threadId（uuid）。已经属于别的账号时 403，不扣费
     * @bodyParam deep boolean 是否深度思考（价格不同）。Default: false
     * @bodyParam device_id string 设备匿名 id（uuid）。游客必填
     * @bodyParam client_ip string 客户端 IP（runtime 写入）。游客必填，没有就 422；只对游客用于同 IP 上限
     */
    public function store(StoreAgentChargeRequest $request)
    {
        $data = $request->validated();
        $account = $this->accounts->resolve($this->currentUserUid($request), $data['device_id'] ?? null);
        $this->accounts->assertThreadOwnable($account, $data['thread_id'] ?? null);
        $charge = $this->credits->charge(
            $account,
            $data['run_id'],
            (bool) ($data['deep'] ?? false),
            $data['client_ip'] ?? null,
        );

        return AgentChargeResource::make($charge)->response()->setStatusCode($charge['created'] ? 201 : 200);
    }
}
