<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/agent-grants` 的入参：给哪个模型、在哪个 channel（可限 book）上授权。
 *
 * 目前只有 channel 一种范围：句子、逐词、句子上的批注都落在 channel 里。
 */
class StoreAgentGrantRequest extends FormRequest
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
            'ai_model' => ['required', 'uuid'],
            'target_type' => ['required', 'string', Rule::in(['channel'])],
            'target_id' => ['required', 'uuid'],
            'book' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
