<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `DELETE /v3/agent-feedback/{message}` 的入参：游客带设备匿名 id。
 */
class DestroyAgentFeedbackRequest extends FormRequest
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
            'device_id' => ['nullable', 'uuid'],
        ];
    }
}
