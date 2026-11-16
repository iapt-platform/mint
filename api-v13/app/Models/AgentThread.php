<?php

namespace App\Models;

use App\Models\Concerns\OwnedByAgentAccount;
use App\Services\V3\AgentAccountService;
use App\Services\V3\AgentThreadService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AI 对话的一个会话（AG-UI thread）。
 *
 * 归属由 account_type（user / guest）+ account_id（用户 uuid / 设备匿名 uuid）表达，按账号查询走
 * `ownedBy()`（{@see OwnedByAgentAccount}）。
 * thread_id（AG-UI threadId）由客户端生成、全局唯一，属于别的账号就拒绝写入（403，
 * {@see AgentAccountService::assertThreadOwnable()}）。游客登录 / 退出后
 * 客户端换新的 threadId——游客会话不并入账号。
 *
 * 软删除＝用户在历史里删掉了它：对用户隐藏，它的提问与步骤一起软删（{@see AgentThreadService::hide()}），
 * 数据永久保留。外键不级联：数据库层面不做任何真删除。
 */
class AgentThread extends Model
{
    use HasFactory, OwnedByAgentAccount, SoftDeletes;

    public const CLIENTS = ['mobile', 'web'];

    /**
     * 客户端能改的只有 title（UpdateMeAgentThreadRequest 只放行它）；其余列由 Service 写。
     */
    protected $guarded = ['id'];

    protected $casts = [
        'archived_at' => 'datetime',
        'last_run_at' => 'datetime',
    ];

    /**
     * @return HasMany<AgentRun, $this>
     */
    public function runs(): HasMany
    {
        return $this->hasMany(AgentRun::class);
    }
}
