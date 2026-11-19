<?php

namespace App\Models;

use App\Models\Concerns\OwnedByAgentAccount;
use App\Services\V3\CreditService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 积分流水的一条。余额 = 流水汇总，见 {@see CreditService}。
 */
class CreditLedgerEntry extends Model
{
    use OwnedByAgentAccount;

    public const KINDS = ['earn', 'spend', 'refund', 'adjust'];

    public const BUCKETS = ['free', 'earned'];

    protected $table = 'credit_ledger';

    protected $guarded = ['id'];

    protected $casts = [
        'day' => 'date:Y-m-d',
        'delta' => 'integer',
    ];

    /**
     * 账号对应的用户。只对 account_type = user 有意义：游客的 account_id 是设备 id，碰巧与某个用户 uid 相同也不能当成他。
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'account_id', 'userid');
    }

    /**
     * 手工调整的操作人（管理员）；系统产生的流水为空。
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(UserInfo::class, 'operator_uid', 'userid');
    }
}
