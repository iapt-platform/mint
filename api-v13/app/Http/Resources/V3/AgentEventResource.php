<?php

namespace App\Http\Resources\V3;

use App\Models\AgentEvent;
use Illuminate\Http\Request;

/**
 * 一条隐式信号的回执。
 *
 * @mixin AgentEvent
 */
class AgentEventResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{message_id: string, kind: string, created_at: string}
     */
    public function toArray($request): array
    {
        return [
            'message_id' => $this->message_id,
            'kind' => $this->kind,
            'created_at' => $this->created_at,
        ];
    }
}
