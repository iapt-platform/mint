<?php

namespace App\Http\Resources\V3;

use App\Models\AgentThread;
use Illuminate\Http\Request;

/**
 * 我的一个 AI 对话会话。列表里不带 runs，详情里带全部可见的问答轮次。
 *
 * deleted_at 只在删除接口的响应里非空（软删后返回的就是这条记录本身）。
 *
 * @mixin AgentThread
 */
class AgentThreadResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     thread_id: string,
     *     title: string,
     *     client: string|null,
     *     archived_at: string|null,
     *     last_run_at: string,
     *     created_at: string,
     *     updated_at: string,
     *     deleted_at: string|null,
     *     runs?: array<int, array{run_id: string, question: string, answer: string|null, citations: array<int, object>|null, deep: bool, status: string, user_message_id: string|null, assistant_message_id: string|null, created_at: string}>
     * }
     */
    public function toArray($request): array
    {
        return [
            'thread_id' => $this->thread_id,
            'title' => $this->title,
            'client' => $this->client,
            'archived_at' => $this->archived_at,
            'last_run_at' => $this->last_run_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'runs' => AgentThreadRunResource::collection($this->whenLoaded('runs')),
        ];
    }
}
