<?php

namespace App\Http\Resources\V3;

use App\Models\AgentRun;
use Illuminate\Http\Request;

/**
 * 写入研究过程后的回执：只回定位用的键，不把证据、回答这些大字段原样吐回去。
 *
 * @mixin AgentRun
 */
class AgentTraceResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     run_id: string,
     *     thread_id: string,
     *     account_type: 'user'|'guest',
     *     created_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'run_id' => $this->run_id,
            'thread_id' => $this->thread->thread_id,
            'account_type' => $this->account_type,
            'created_at' => $this->created_at,
        ];
    }
}
