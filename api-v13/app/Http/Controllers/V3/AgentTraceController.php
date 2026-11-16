<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreAgentTraceRequest;
use App\Http\Resources\V3\AgentTraceResource;
use App\Services\V3\AgentTraceService;

/**
 * AI 对话的研究过程入库。只给 agent 服务调（`auth.agent-service`），不对客户端开放。
 *
 * 新资源，没有 v2 前身。
 */
class AgentTraceController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly AgentTraceService $traces) {}

    /**
     * 写入一次提问的研究过程（幂等）
     *
     * 请求头 `X-Agent-Service-Key` 证明调用方是 agent 服务；`Authorization: Bearer`
     * 是 agent 透传的终端用户 token（可选）：有效的人类 token 记到账号下，
     * 没有 / 失效 / 是模型 token 都记为游客——此时 `device_id` 必填，并记 `client_ip`。
     * token 失效不拒绝，照样入库。
     *
     * 按 run_id 幂等：首次写入 201，重复提交返回已有记录 200，步骤不会重复写。
     *
     * @responseStatus 201 首次写入。响应体与 200 同形
     * @responseStatus 403 thread_id 已经属于别的账号
     *
     * @bodyParam run_id string required AG-UI runId（uuid）
     * @bodyParam thread_id string required AG-UI threadId（uuid）
     * @bodyParam device_id string 设备匿名 id（uuid）。游客必填，登录用户忽略
     * @bodyParam client_ip string 客户端 IP。只对游客记录
     * @bodyParam client string 客户端。Enum: mobile,web
     * @bodyParam question string required 用户的提问
     * @bodyParam status string required 运行结果（denied＝额度不足被拦下）。Enum: ok,error,cancelled,denied
     * @bodyParam improve boolean 用户是否允许用于改进回答（提问那一刻的开关）。Default: true
     * @bodyParam steps array 每一步（LLM 调用、工具调用、节点事件），最多 500 条
     */
    public function store(StoreAgentTraceRequest $request)
    {
        return AgentTraceResource::make(
            $this->traces->record($this->currentUserUid($request), $request->validated())
        );
    }
}
