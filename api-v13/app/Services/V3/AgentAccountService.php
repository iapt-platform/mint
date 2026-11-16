<?php

namespace App\Services\V3;

use App\Models\AgentThread;
use App\Models\AiModel;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * AI 对话里「这次提问算谁的」：研究过程入库、额度、积分共用同一套口径。
 *
 * - 有效的人类 token → 登录用户（type user，id = 用户 uuid）；
 * - 没有 token、token 失效、或是 AI 模型 token → 游客（type guest，id = 设备匿名 uuid），
 *   必须带设备匿名 id。
 *
 * 结果是 {@see AgentAccount}，落库是 account_type + account_id 两列。
 *
 * 模型 token 也能通过 JWT 校验，但对话归属、额度都只认人类账号。
 */
class AgentAccountService
{
    /**
     * @throws ValidationException 游客没带 device_id
     */
    public function resolve(?string $bearerUid, ?string $deviceId): AgentAccount
    {
        $userUid = $this->humanUid($bearerUid);
        if ($userUid) {
            return AgentAccount::user($userUid);
        }
        if (! $deviceId) {
            throw ValidationException::withMessages([
                'device_id' => __('messages.agent_device_required'),
            ]);
        }

        return AgentAccount::guest($deviceId);
    }

    /**
     * 会话 id（AG-UI threadId）由客户端生成、全局唯一。已经属于别的账号的 id 不准再用：
     * 扣费时就拦下（不跑、不扣），入库时再兜一次。同账号的已删除会话不算别人的。
     *
     * 游客登录（或退出）后客户端会换一个新的 threadId，所以这里不会误伤正常用户。
     */
    public function assertThreadOwnable(AgentAccount $account, ?string $threadId): void
    {
        if (! $threadId) {
            return;
        }
        $thread = AgentThread::withTrashed()->where('thread_id', $threadId)->first(['account_type', 'account_id']);
        if ($thread !== null && ! $thread->isOwnedBy($account)) {
            abort(403, __('messages.agent_thread_forbidden'));
        }
    }

    private function humanUid(?string $uid): ?string
    {
        if (! $uid || ! Str::isUuid($uid)) {
            return null;
        }

        return AiModel::where('uid', $uid)->exists() ? null : $uid;
    }
}
