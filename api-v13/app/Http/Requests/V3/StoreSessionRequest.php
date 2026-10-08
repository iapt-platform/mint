<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/sessions` 的入参：用户名或邮箱 + 密码。
 */
class StoreSessionRequest extends FormRequest
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
            // 用户名（varchar 64）或邮箱（varchar 256）
            'login' => ['required', 'string', 'max:256'],
            'password' => ['required', 'string', 'max:64'],
        ];
    }
}
