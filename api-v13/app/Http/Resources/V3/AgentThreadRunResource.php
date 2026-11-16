<?php

namespace App\Http\Resources\V3;

use App\Models\AgentRun;
use Illuminate\Http\Request;

/**
 * 会话历史里的一轮问答：用户看得见的那部分。
 *
 * 研究过程（路由、计划、证据、步骤）不在这里——那是改进回答与防滥用用的，
 * 不回给终端用户。user_message_id / assistant_message_id 是 AG-UI 消息 id，
 * 续聊时客户端用它们把旧消息原样回灌给 agent。
 *
 * @mixin AgentRun
 */
class AgentThreadRunResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     run_id: string,
     *     question: string,
     *     answer: string|null,
     *     citations: array<int, object>|null,
     *     deep: bool,
     *     status: string,
     *     user_message_id: string|null,
     *     assistant_message_id: string|null,
     *     created_at: string
     * }
     */
    public function toArray($request): array
    {
        return [
            'run_id' => $this->run_id,
            'question' => $this->question,
            'answer' => $this->answer,
            'citations' => $this->citations,
            'deep' => $this->deep,
            'status' => $this->status,
            'user_message_id' => $this->user_message_id,
            'assistant_message_id' => $this->assistant_message_id,
            'created_at' => $this->started_at ?? $this->created_at,
        ];
    }
}
