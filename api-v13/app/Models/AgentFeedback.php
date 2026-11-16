<?php

namespace App\Models;

use App\Models\Concerns\OwnedByAgentAccount;
use Illuminate\Database\Eloquent\Model;

/**
 * 用户对一条 AI 回答的评价（按 AG-UI 回答消息 id）。一人一条，可改可撤回。
 */
class AgentFeedback extends Model
{
    use OwnedByAgentAccount;

    /** 点赞可选的标签。 */
    public const UP_TAGS = ['accurate', 'useful_citations', 'clear', 'inspiring'];

    /** 点踩的原因。other 带一段文字说明（comment）。 */
    public const DOWN_REASONS = ['citation_wrong', 'missed', 'off_topic', 'incorrect', 'too_long', 'too_academic', 'slow', 'other'];

    protected $table = 'agent_feedback';

    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array',
        'asked' => 'boolean',
        'rating' => 'integer',
    ];
}
