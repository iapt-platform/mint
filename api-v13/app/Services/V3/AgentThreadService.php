<?php

namespace App\Services\V3;

use App\Models\AgentRun;
use App\Models\AgentRunStep;
use App\Models\AgentThread;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * 登录用户的 AI 对话会话历史：列表、详情、改名、存档、删除。
 *
 * 只认登录用户的账号（account_type = user）——游客没有历史接口（设备 id 不是秘密，不能凭它读）。
 * 别人的会话与不存在的会话一样回 404，不暴露「这个 thread 有主」。
 */
class AgentThreadService
{
    /**
     * 我的会话列表，最近提问的在前。用户删除过的不列。
     *
     * @param  array{filter?: array{archived?: bool|string}}  $filters
     * @return LengthAwarePaginator<int, AgentThread>
     */
    public function listForUser(string $userUid, array $filters, int $perPage): LengthAwarePaginator
    {
        $archived = $filters['filter']['archived'] ?? null;

        return AgentThread::ownedBy(AgentAccount::user($userUid))
            ->when($archived !== null, fn ($q) => filter_var($archived, FILTER_VALIDATE_BOOLEAN)
                ? $q->whereNotNull('archived_at')
                : $q->whereNull('archived_at'))
            ->orderByDesc('last_run_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * 一个会话及其全部可见的问答轮次（被「重新生成」替换掉的不算），按提问先后排。
     */
    public function showForUser(string $userUid, string $threadId): AgentThread
    {
        return $this->findForUser($userUid, $threadId)->load([
            'runs' => fn ($q) => $q->visible()->orderBy('started_at')->orderBy('id'),
        ]);
    }

    /**
     * @param  array{title?: string}  $data
     */
    public function update(string $userUid, string $threadId, array $data): AgentThread
    {
        $thread = $this->findForUser($userUid, $threadId);
        $thread->fill($data)->save();

        return $thread;
    }

    /**
     * 存档 / 取消存档。重复存档保留第一次的时间。
     */
    public function setArchived(string $userUid, string $threadId, bool $archived): AgentThread
    {
        $thread = $this->findForUser($userUid, $threadId);
        if ($archived && $thread->archived_at === null) {
            $thread->archived_at = now();
        } elseif (! $archived) {
            $thread->archived_at = null;
        }
        $thread->save();

        return $thread;
    }

    /**
     * 删除＝软删会话，连同它的提问与步骤一起软删：之后任何接口（含将来的分享）都读不到，
     * 数据永久保留（研究过程要留着防滥用、改进回答）。
     */
    public function hide(string $userUid, string $threadId): AgentThread
    {
        $thread = $this->findForUser($userUid, $threadId);

        DB::transaction(function () use ($thread) {
            $runIds = AgentRun::where('agent_thread_id', $thread->id)->pluck('id');
            AgentRunStep::whereIn('agent_run_id', $runIds)->delete();
            AgentRun::whereIn('id', $runIds)->delete();
            $thread->delete();
        });

        return $thread;
    }

    /**
     * @throws ModelNotFoundException 不存在、不是我的、或已删除 → 404
     */
    private function findForUser(string $userUid, string $threadId): AgentThread
    {
        return AgentThread::ownedBy(AgentAccount::user($userUid))
            ->where('thread_id', $threadId)
            ->firstOrFail();
    }
}
