<?php

namespace App\Http\Requests\V3;

use App\Models\GroupMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/groups/{group}/members` 的入参：加一名成员，角色默认 reader。owner 不能经此产生。
 */
class StoreGroupMemberRequest extends FormRequest
{
    /**
     * 登录与权限在中间件 / Service 里判：authorize() 返回 false 会渲染成 403，
     * 而「没登录」应该是 401、「不是成员」要带具体文案。
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
            'user_id' => ['required', 'uuid'],
            'role' => ['sometimes', 'string', Rule::in(GroupMember::ASSIGNABLE_ROLES)],
        ];
    }
}
