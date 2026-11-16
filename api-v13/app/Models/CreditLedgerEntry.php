<?php

namespace App\Models;

use App\Models\Concerns\OwnedByAgentAccount;
use App\Services\V3\CreditService;
use Illuminate\Database\Eloquent\Model;

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
}
