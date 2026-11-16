<?php

namespace App\Models\Concerns;

use App\Services\V3\AgentAccount;
use Illuminate\Contracts\Database\Eloquent\Builder;

/**
 * 带 `account_type` + `account_id` 两列（AI 对话 / 积分的账号）的模型。
 *
 * 按账号查询只准用 {@see scopeOwnedBy()}：两列一起比，游客把设备 id 填成某个用户的 uid 也冒充不了他。
 *
 * @property string $account_type user / guest
 * @property string $account_id 用户 uuid 或设备匿名 uuid
 */
trait OwnedByAgentAccount
{
    public function scopeOwnedBy(Builder $query, AgentAccount $account): void
    {
        $query->where($this->qualifyColumn('account_type'), $account->type)
            ->where($this->qualifyColumn('account_id'), $account->id);
    }

    public function isOwnedBy(AgentAccount $account): bool
    {
        return $this->account_type === $account->type && $this->account_id === $account->id;
    }

    /**
     * 这一行的账号。
     */
    public function account(): AgentAccount
    {
        return $this->account_type === 'user'
            ? AgentAccount::user($this->account_id)
            : AgentAccount::guest($this->account_id);
    }
}
