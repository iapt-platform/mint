<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexAdminAgentFeedbackRequest;
use App\Http\Resources\V3\AdminAgentFeedbackResource;
use App\Services\V3\AgentFeedbackService;

/**
 * 管理员审阅用户对 AI 回答的评价（仅 root / administrator）。新资源，没有 v2 前身。
 */
class AdminAgentFeedbackController extends Controller
{
    public function __construct(private readonly AgentFeedbackService $feedback) {}

    /**
     * 列出评价
     *
     * 最新的在前，带对应提问的摘要与自动质量指标，便于判断问题在哪。不含账号身份。
     *
     * @queryParam filter[rating] string 1 赞 / -1 踩。Enum: 1,-1
     * @queryParam filter[tag] string 按标签 / 原因筛
     * @queryParam filter[has_comment] boolean 只看带文字说明的
     * @queryParam from string 起始日（UTC，Y-m-d）
     * @queryParam to string 结束日（UTC，Y-m-d，含当天）
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 100。Default: 20
     */
    public function index(IndexAdminAgentFeedbackRequest $request)
    {
        return AdminAgentFeedbackResource::collection(
            $this->feedback->review($request->validated(), $request->integer('per_page', 20))
        );
    }
}
