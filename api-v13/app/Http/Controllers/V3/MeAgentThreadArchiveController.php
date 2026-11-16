<?php

namespace App\Http\Controllers\V3;

use App\Http\Controllers\Concerns\ResolvesCurrentUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\V3\AgentThreadResource;
use App\Services\V3\AgentThreadService;
use Illuminate\Http\Request;

/**
 * 会话的存档状态（单例子资源 `/archive`）。PUT 存档，DELETE 取消存档，都幂等。
 *
 * 存档目前只是分组。
 */
class MeAgentThreadArchiveController extends Controller
{
    use ResolvesCurrentUser;

    public function __construct(private readonly AgentThreadService $threads) {}

    /**
     * 存档会话
     *
     * 重复存档保留第一次的存档时间。返回更新后的会话。
     *
     * @urlParam thread string required AG-UI threadId（uuid）
     */
    public function update(Request $request, string $thread)
    {
        return AgentThreadResource::make(
            $this->threads->setArchived($this->currentUser($request)['user_uid'], $thread, true)
        );
    }

    /**
     * 取消存档
     *
     * 返回更新后的会话（`archived_at` 为 null）。
     *
     * @urlParam thread string required AG-UI threadId（uuid）
     */
    public function destroy(Request $request, string $thread)
    {
        return AgentThreadResource::make(
            $this->threads->setArchived($this->currentUser($request)['user_uid'], $thread, false)
        );
    }
}
