<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexAdminAgentDigestRequest;
use App\Http\Requests\V3\IndexAdminAgentRunRequest;
use App\Http\Resources\V3\AdminAgentRunResource;
use App\Http\Resources\V3\BaseResource;
use App\Services\V3\AgentStatsService;
use App\Services\V3\StatsRange;

/**
 * 管理员查阅 AI 对话的提问与研究过程（仅 root / administrator）。新资源，没有 v2 前身。
 *
 * 也是评测脚本从库里抽题的读接口（只抽 improve=true 的）。
 */
class AdminAgentRunController extends Controller
{
    public function __construct(private readonly AgentStatsService $stats) {}

    /**
     * 列出提问
     *
     * 最新的在前。提问原文对管理员可见；游客只显示设备匿名 id 与 IP。
     *
     * @queryParam filter[status] string 运行结果。Enum: ok,error,cancelled,denied
     * @queryParam filter[route] string 研究路径：question 一般问题 / term 术语与词 / passage 阅读页提问 / chat 闲聊。Enum: question,term,passage,chat
     * @queryParam filter[account_type] string 账号类型。Enum: user,guest
     * @queryParam filter[client] string 客户端。Enum: mobile,web
     * @queryParam filter[improve] boolean 是否允许用于改进回答
     * @queryParam filter[q] string 按提问原文模糊搜索
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 100。Default: 20
     */
    public function index(IndexAdminAgentRunRequest $request)
    {
        return AdminAgentRunResource::collection(
            $this->stats->listRuns($request->validated(), $request->integer('per_page', 20))
        );
    }

    /**
     * 分析用的精简导出
     *
     * 区间内允许用于改进回答（improve=true）的提问，最早的在前。不带任何身份（账号、设备、IP）。
     * 每条给：问题、路由与把握度、检索词、状态与耗时、工具调用序列、用户评价、隐式信号计数、
     * 同一会话里下一次提问隔了几秒；`full=true` 时再带回答、证据全文与引用，供 agent 评估回答质量
     * （服务端不做质量评估）。给 wikipali 插件的 `admin digest` 与分析技能用。
     *
     * @queryParam from string 起始日（UTC，Y-m-d）。Default: 今天往前 29 天
     * @queryParam to string 结束日（UTC，Y-m-d，含当天）。Default: 今天
     * @queryParam full boolean 是否带回答、证据全文与引用（此时 limit 最大 200）。Default: false
     * @queryParam limit integer 最多多少条，最大 2000（full 时 200）。Default: 500（full 时 100）
     *
     * @meta has_more boolean 区间内是否还有更多（超出 limit）
     */
    public function digest(IndexAdminAgentDigestRequest $request)
    {
        [$from, $to] = StatsRange::resolve($request->validated());
        $full = $request->boolean('full');
        $digest = $this->stats->digest($from, $to, $request->integer('limit', $full ? 100 : 500), $full);

        return BaseResource::collection($digest['items'])->additional(['meta' => [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'count' => count($digest['items']),
            'has_more' => $digest['has_more'],
        ]]);
    }

    /**
     * 查看一次提问的研究过程
     *
     * 带回答、给作答 LLM 的证据全文、计划、引用与全部步骤（LLM / 工具调用、节点事件）。
     *
     * @urlParam run string required AG-UI runId（uuid）
     */
    public function show(string $run)
    {
        return AdminAgentRunResource::make($this->stats->showRun($run));
    }
}
