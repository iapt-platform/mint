<?php

namespace App\Http\Requests\V3;

use App\Models\AgentEvent;
use App\Models\AgentThread;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/agent-events` 的入参：AI 回答上的一条隐式信号。
 */
class StoreAgentEventRequest extends FormRequest
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
            'kind' => ['required', Rule::in(AgentEvent::KINDS)],
            'device_id' => ['nullable', 'uuid'],
            'client' => ['nullable', Rule::in(AgentThread::CLIENTS)],
        ];
    }
}
