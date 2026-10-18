<?php

namespace App\Http\Requests\V3;

use App\Models\Discussion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/discussions` 的入参：某个对象上的话题列表。
 *
 * target 必填：discussions 没有全表可读的用例，不带条件就能拉全表的列表端点是设计错误。
 */
class IndexDiscussionRequest extends FormRequest
{
    /**
     * 可选登录，权限在 Service 里按查看者过滤。
     */
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
            'status' => ['sometimes', 'string', Rule::in(Discussion::STATUSES)],
            // 逗号分隔多选，如 error,warning
            'severity' => ['sometimes', 'string', 'regex:/^[a-z]+(,[a-z]+)*$/'],
            'sort' => ['sometimes', 'string', Rule::in(['created_at', '-created_at', 'updated_at', '-updated_at'])],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
