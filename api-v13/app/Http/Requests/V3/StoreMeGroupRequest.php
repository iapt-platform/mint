<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/me/groups` 的入参：凭邀请链接里的 token 加入团队。
 */
class StoreMeGroupRequest extends FormRequest
{
    /**
     * 登录在中间件里判，链接是否有效在 Service 里判（无效是 422 errors.invite）。
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
            'invite' => ['required', 'string', 'max:2048'],
        ];
    }
}
