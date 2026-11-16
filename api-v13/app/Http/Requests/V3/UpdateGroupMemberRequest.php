<?php

namespace App\Http\Requests\V3;

use App\Models\GroupMember;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `PATCH /v3/groups/{group}/members/{member}` 的入参：改成员角色。只做局部更新，没有必填项。
 */
class UpdateGroupMemberRequest extends FormRequest
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
            'role' => ['sometimes', 'string', Rule::in(GroupMember::ASSIGNABLE_ROLES)],
        ];
    }
}
