<?php

namespace App\Http\Resources\V3;

use App\Models\AgentFeedback;
use Illuminate\Http\Request;

/**
 * 我对一条 AI 回答的评价。
 *
 * @mixin AgentFeedback
 */
class AgentFeedbackResource extends BaseResource
{
    /**
     * @param  Request  $request
     * @return array{message_id: string, rating: int, tags: array<int, string>, comment: string|null, asked: bool, updated_at: string}
     */
    public function toArray($request): array
    {
        return [
            'message_id' => $this->message_id,
            'rating' => $this->rating,
            'tags' => $this->tags ?? [],
            'comment' => $this->comment,
            'asked' => $this->asked,
            'updated_at' => $this->updated_at,
        ];
    }
}
