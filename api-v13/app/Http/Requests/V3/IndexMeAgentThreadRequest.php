<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/me/agent-threads` 的入参：当前用户自己的 AI 对话会话列表。
 */
class IndexMeAgentThreadRequest extends FormRequest
{
    /**
     * 登录检查由 auth.v3 中间件做：authorize() 返回 false 会渲染成 403，
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
            'filter.archived' => ['sometimes', 'in:true,false,1,0'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}
