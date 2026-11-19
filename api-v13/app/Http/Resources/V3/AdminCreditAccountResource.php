<?php

namespace App\Http\Resources\V3;

use App\Http\Resources\Concerns\ResolvesActor;
use App\Models\CreditLedgerEntry;
use Illuminate\Http\Request;

/**
 * 一个账号的积分汇总（按账号聚合的流水，不是单条流水）。**本类不查库**：user 由 Service 预加载。
 *
 * @mixin CreditLedgerEntry
 */
class AdminCreditAccountResource extends BaseResource
{
    use ResolvesActor;

    /**
     * @param  Request  $request
     * @return array{
     *     account_type: 'user'|'guest',
     *     account_id: string,
     *     user: array{id: string, nickName: string, userName: string, realName: string, sn: int, avatar: string|null, roles: array|null}|null,
     *     earned_balance: int,
     *     earned_total: int,
     *     adjusted_total: int,
     *     spent_total: int,
     *     free_used_today: int,
     *     daily_free: int,
     *     last_activity_at: string|null
     * }
     */
    public function toArray($request): array
    {
        return [
            'account_type' => $this->account_type,
            'account_id' => $this->account_id,
            // 游客的 account_id 是设备 id，不能拿去当用户 uid
            'user' => $this->account_type === 'user' && $this->user ? $this->actor($this->user) : null,
            'earned_balance' => (int) $this->earned_balance,
            'earned_total' => (int) $this->earned_total,
            'adjusted_total' => (int) $this->adjusted_total,
            'spent_total' => (int) $this->spent_total,
            'free_used_today' => (int) $this->free_used_today,
            'daily_free' => (int) (config('mint.credits.policy.daily_free')[$this->account_type] ?? 0),
            'last_activity_at' => $this->last_activity_at ? now('UTC')->parse($this->last_activity_at)->toIso8601ZuluString() : null,
        ];
    }
}
