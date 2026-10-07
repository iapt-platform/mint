<?php

namespace App\Http\Requests\V3;

use App\Models\Reaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/me/reactions` 的入参：添加一条自己的 reaction。
 *
 * 不收 user_id：v2 允许 `user_id` 覆盖操作者（替别人加关注），那是 target 的
 * 关注者管理，不属于 `me` 这组端点，留在 v2。
 */
class StoreMeReactionRequest extends FormRequest
{
    /**
     * 登录检查在控制器里做：authorize() 返回 false 会渲染成 403，
     * 而「没登录」应该是 401。
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
            'type' => ['required', 'string', Rule::in(Reaction::TYPES)],
            'target_id' => ['required', 'uuid'],
            'target_type' => ['required', 'string', Rule::in(Reaction::TARGET_TYPES)],
            // context 列是 varchar(128)
            'context' => ['nullable', 'string', 'max:128'],
        ];
    }
}
