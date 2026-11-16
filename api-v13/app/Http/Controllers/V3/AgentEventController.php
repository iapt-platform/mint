<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\StoreAgentEventRequest;
use App\Http\Resources\V3\AgentEventResource;
use App\Services\V3\AgentFeedbackService;

/**
 * AI 回答上的隐式信号（复制、点开引用、重新生成、主动询问显示过）。新资源，没有 v2 前身。
 */
class AgentEventController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly AgentFeedbackService $feedback) {}

    /**
     * 上报一条隐式信号
     *
     * @unauthenticated
     *
     * @bodyParam message_id string required AG-UI 回答消息 id
     * @bodyParam kind string required 信号。Enum: copy,citation_open,regenerate,ask_shown
     * @bodyParam device_id string 设备匿名 id（uuid）。未登录时必填
     * @bodyParam client string 客户端。Enum: mobile,web
     */
    public function store(StoreAgentEventRequest $request)
    {
        return AgentEventResource::make($this->feedback->event($this->currentUserUid($request), $request->validated()));
    }
}
