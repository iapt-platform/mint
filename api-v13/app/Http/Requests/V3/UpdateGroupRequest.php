<?php

namespace App\Http\Requests\V3;

use App\Models\GroupInfo;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `PATCH /v3/groups/{group}` 的入参：改团队信息。只做局部更新，没有必填项。
 */
class UpdateGroupRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:64'],
            'description' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'status' => ['sometimes', 'integer', 'in:'.GroupInfo::STATUS_PRIVATE.','.GroupInfo::STATUS_PUBLIC],
        ];
    }
}
