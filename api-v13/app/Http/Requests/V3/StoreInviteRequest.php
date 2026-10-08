<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/invites` 的入参：用邮箱验证码换一条注册邀请。
 */
class StoreInviteRequest extends FormRequest
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
            'email' => ['required', 'email', 'max:256'],
            'code' => ['required', 'digits:6'],
        ];
    }
}
