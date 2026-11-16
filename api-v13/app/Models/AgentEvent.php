<?php

namespace App\Models;

use App\Models\Concerns\OwnedByAgentAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * AI 回答上的隐式信号（复制、点开引用、重新生成、主动询问显示过）。
 */
class AgentEvent extends Model
{
    use OwnedByAgentAccount;

    public const KINDS = ['copy', 'citation_open', 'regenerate', 'ask_shown'];

    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
