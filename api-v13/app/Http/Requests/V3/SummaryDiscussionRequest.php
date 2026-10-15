<?php

namespace App\Http\Requests\V3;

use App\Models\Discussion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/discussions/summary` 的入参：某个对象上的话题概况。
 */
class SummaryDiscussionRequest extends FormRequest
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
            'target_type' => ['required', 'string', Rule::in(Discussion::TARGET_TYPES)],
            'target_id' => ['required', 'uuid'],
            'type' => ['sometimes', 'string', Rule::in(Discussion::COMMENT_TYPES)],
        ];
    }
}
