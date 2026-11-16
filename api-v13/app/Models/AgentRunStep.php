<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 一次提问里的一步（LLM 调用、工具调用或节点事件）。
 *
 * 软删除：随所属提问一起软删，数据永久保留。
 */
class AgentRunStep extends Model
{
    use SoftDeletes;

    /**
     * 已知的步骤类型。入库时不认识的类型照样存（只记警告，见 AgentTraceService::record），
     * 免得 backend 新加一种步骤就整条研究过程被拒。
     *
     * @var list<string>
     */
    public const KINDS = ['llm', 'tool', 'route', 'plan', 'retry', 'judge', 'passage', 'answer', 'budget', 'event'];

    public $timestamps = false;

    protected $guarded = ['id'];

    protected $casts = [
        'args' => 'array',
        'started_at' => 'datetime',
    ];
}
