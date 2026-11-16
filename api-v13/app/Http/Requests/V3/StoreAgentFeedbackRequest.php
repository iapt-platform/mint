<?php

namespace App\Http\Requests\V3;

use App\Models\AgentFeedback;
use App\Models\AgentThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/agent-feedback` 的入参：对一条 AI 回答点赞 / 点踩。
 */
class StoreAgentFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'message_id' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9_\-]+$/'],
            'thread_id' => ['nullable', 'uuid'],
            'rating' => ['required', 'integer', Rule::in([1, -1])],
            'tags' => ['nullable', 'array', 'max:8'],
            'tags.*' => ['string', Rule::in([...AgentFeedback::UP_TAGS, ...AgentFeedback::DOWN_REASONS])],
            'comment' => ['nullable', 'string', 'max:1000'],
            'asked' => ['boolean'],
            'device_id' => ['nullable', 'uuid'],
            'client' => ['nullable', Rule::in(AgentThread::CLIENTS)],
        ];
    }
}
