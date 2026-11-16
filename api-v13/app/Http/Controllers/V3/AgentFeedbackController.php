<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\DestroyAgentFeedbackRequest;
use App\Http\Requests\V3\StoreAgentFeedbackRequest;
use App\Http\Resources\V3\AgentFeedbackResource;
use App\Services\V3\AgentFeedbackService;

/**
 * 用户对 AI 回答的评价（点赞 / 点踩）。可选登录：登录用户按账号，游客带设备匿名 id。
 * 新资源，没有 v2 前身。
 */
class AgentFeedbackController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly AgentFeedbackService $feedback) {}

    /**
     * 评价一条回答（再次提交即更新）
     *
     * 一人对一条回答只留一条：首次 201，更新 200。点赞的 tags 取 accurate / useful_citations / clear / inspiring，
     * 点踩的 tags 是原因：citation_wrong / missed / off_topic / incorrect / too_long / too_academic / slow / other；
     * 只有选了 other 时 comment 才会保存。asked＝这条是被主动询问问出来的。
     *
     * @unauthenticated
     *
     * @responseStatus 201 首次评价。响应体与 200 同形
     *
     * @bodyParam message_id string required AG-UI 回答消息 id
     * @bodyParam thread_id string 会话 id（uuid）
     * @bodyParam rating integer required 1 赞 / -1 踩（只能是这两个值）。
     * @bodyParam tags array 点赞标签或点踩原因
     * @bodyParam comment string 选了 other 时的文字说明，最长 1000 字
     * @bodyParam asked boolean 是否主动询问问出来的
     * @bodyParam device_id string 设备匿名 id（uuid）。未登录时必填
     * @bodyParam client string 客户端。Enum: mobile,web
     */
    public function store(StoreAgentFeedbackRequest $request)
    {
        return AgentFeedbackResource::make($this->feedback->rate($this->currentUserUid($request), $request->validated()));
    }

    /**
     * 撤回评价
     *
     * 没评价过也返回 204（幂等）。
     *
     * @unauthenticated
     *
     * @urlParam message string required AG-UI 回答消息 id
     *
     * @queryParam device_id string 设备匿名 id（uuid）。未登录时必填
     *
     * @responseStatus 204 已撤回，无响应体
     */
    public function destroy(DestroyAgentFeedbackRequest $request, string $message)
    {
        $this->feedback->retract($this->currentUserUid($request), $message, $request->validated('device_id'));

        return response()->noContent();
    }
}
