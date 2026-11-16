<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Request;

/**
 * 管理员审阅的一条评价，带对应提问的摘要（提问还没入库时 run_id 等为空）。
 * 不给账号身份，只给账号类型。
 */
class AdminAgentFeedbackResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{
     *     message_id: string,
     *     rating: int,
     *     tags: array<int, string>,
     *     comment: string|null,
     *     asked: bool,
     *     client: string|null,
     *     account_type: string,
     *     updated_at: string,
     *     run_id: string|null,
     *     question: string|null,
     *     route: 'question'|'term'|'passage'|'chat'|null,
     *     status: string|null,
     *     deep: bool|null,
     *     latency_ms: int|null
     * }
     */
    public function toArray($request): array
    {
        $r = $this->resource;

        return [
            'message_id' => $r->message_id,
            'rating' => (int) $r->rating,
            'tags' => $r->tags,
            'comment' => $r->comment,
            'asked' => (bool) $r->asked,
            'client' => $r->client,
            'account_type' => $r->account_type,
            'updated_at' => $r->updated_at,
            'run_id' => $r->run_id,
            'question' => $r->question,
            'route' => $r->route,
            'status' => $r->status,
            'deep' => $r->deep === null ? null : (bool) $r->deep,
            'latency_ms' => $r->latency_ms === null ? null : (int) $r->latency_ms,
        ];
    }
}
