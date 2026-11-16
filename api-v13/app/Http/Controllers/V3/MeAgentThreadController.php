<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\V3\IndexMeAgentThreadRequest;
use App\Http\Requests\V3\UpdateMeAgentThreadRequest;
use App\Http\Resources\V3\AgentThreadResource;
use App\Services\V3\AgentThreadService;
use Illuminate\Http\Request;

/**
 * 当前登录用户的 AI 对话会话历史（web chat 与 mobile 共用）。
 *
 * 游客没有历史接口：设备 id 不是秘密，不能凭它读别人的提问。
 * `{thread}` 是 AG-UI threadId；别人的会话与不存在的一样回 404。
 *
 * 新资源，没有 v2 前身（v2 的 `chats` / `chat-messages` 是另一套、未启用的表）。
 */
class MeAgentThreadController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly AgentThreadService $threads) {}

    /**
     * 列出我的会话
     *
     * 最近提问的在前，不含已删除的。
     *
     * @queryParam filter[archived] boolean 只看已存档（true）或未存档（false）的；不传则全部
     * @queryParam page integer 页码。Default: 1
     * @queryParam per_page integer 每页数量，最大 100。Default: 30
     */
    public function index(IndexMeAgentThreadRequest $request)
    {
        return AgentThreadResource::collection($this->threads->listForUser(
            $this->currentUser($request)['user_uid'],
            $request->validated(),
            $request->integer('per_page', 30),
        ));
    }

    /**
     * 查看一个会话
     *
     * 带全部可见的问答轮次（runs），按提问先后排；被「重新生成」替换掉的轮次不返回。
     *
     * @urlParam thread string required AG-UI threadId（uuid）
     */
    public function show(Request $request, string $thread)
    {
        return AgentThreadResource::make(
            $this->threads->showForUser($this->currentUser($request)['user_uid'], $thread)
        );
    }

    /**
     * 修改会话（目前只有标题）
     *
     * @urlParam thread string required AG-UI threadId（uuid）
     *
     * @bodyParam title string 会话标题，1–255 字
     */
    public function update(UpdateMeAgentThreadRequest $request, string $thread)
    {
        return AgentThreadResource::make($this->threads->update(
            $this->currentUser($request)['user_uid'],
            $thread,
            $request->validated(),
        ));
    }

    /**
     * 删除会话
     *
     * 软删：会话连同它的问答一起从我的历史里消失，之后任何接口都读不到；服务器上的数据永久保留（防滥用、改进回答）。
     * 返回软删后的会话本身（200，`deleted_at` 非空），不是 204。
     *
     * @urlParam thread string required AG-UI threadId（uuid）
     */
    public function destroy(Request $request, string $thread)
    {
        return AgentThreadResource::make(
            $this->threads->hide($this->currentUser($request)['user_uid'], $thread)
        );
    }
}
