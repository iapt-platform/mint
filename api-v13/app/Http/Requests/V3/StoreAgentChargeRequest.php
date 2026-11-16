<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/agent-charges` 的入参：agent 服务在一次提问开始前为它扣费。
 */
class StoreAgentChargeRequest extends FormRequest
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
            'run_id' => ['required', 'uuid'],
            'thread_id' => ['nullable', 'uuid'],
            'deep' => ['boolean'],
            'device_id' => ['nullable', 'uuid'],
            'client_ip' => ['nullable', 'ip'],
        ];
    }
}
