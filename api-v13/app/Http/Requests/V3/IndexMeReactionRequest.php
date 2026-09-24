<?php

namespace App\Http\Requests\V3;

use App\Models\Reaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/me/reactions` 的入参：当前用户自己的 reaction 列表。
 *
 * 这里没有 user_id——结果集永远由登录态决定，不接受客户端指定。
 */
class IndexMeReactionRequest extends FormRequest
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
            'type' => ['nullable', 'string', Rule::in(Reaction::TYPES)],
            'target_type' => ['nullable', 'string', Rule::in(Reaction::TARGET_TYPES)],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
