<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/users` 的入参：凭邀请注册账号。
 *
 * 邮箱不在这里收：它来自 invite，客户端改不了。
 * invite 是否可用在 Service 里查（要连带检查邮箱是否已被注册，规则写不干净）。
 */
class StoreUserRequest extends FormRequest
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
            'invite' => ['required', 'uuid'],
            // v2 前端的规则是 6–32 位字母数字下划线，但正则漏了 $，后端也不校验；这里补齐
            'username' => ['required', 'string', 'min:6', 'max:32', 'regex:/^[A-Za-z0-9_]+$/', 'unique:user_infos,username'],
            // 不传或空白时用 username
            'nickname' => ['sometimes', 'nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:6', 'max:32', 'confirmed'],
            // draft channel 的语言，channels.lang 是 varchar(16)
            'lang' => ['required', 'string', 'max:16'],
        ];
    }
}
