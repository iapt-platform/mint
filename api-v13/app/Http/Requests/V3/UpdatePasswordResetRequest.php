<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `PATCH /v3/password-resets/{token}` 的入参：设新密码。
 *
 * 长度沿用注册时的 6–32。不用 `Password::defaults()`：它在生产环境要求 12 位
 * 混合字符并联网查泄露库，与注册规则不一致，要收紧得注册、重置一起改。
 */
class UpdatePasswordResetRequest extends FormRequest
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
            'password' => ['required', 'string', 'min:6', 'max:32', 'confirmed'],
        ];
    }
}
