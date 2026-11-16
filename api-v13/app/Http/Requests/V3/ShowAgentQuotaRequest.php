<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/agent-quota` 的入参。登录用户靠 bearer，游客带设备匿名 id。
 */
class ShowAgentQuotaRequest extends FormRequest
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
