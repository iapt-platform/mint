<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/groups/{group}/members` 的入参：团队成员列表。
 */
class IndexGroupMemberRequest extends FormRequest
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
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
