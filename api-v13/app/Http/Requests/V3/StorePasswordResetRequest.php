<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/password-resets` 的入参：申请发送重置邮件。
 *
 * 不收 v2 那个 `dashboard` 参数：重置链接一律用服务端配置拼。
 */
class StorePasswordResetRequest extends FormRequest
{
    /**
     * 匿名端点，没有授权可言。
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
            // user_infos.email 是 varchar(256)
            'email' => ['required', 'email', 'max:256'],
            // 邮件语言，没有对应模板的回落 en，所以这里不限定取值
            'lang' => ['sometimes', 'string', 'max:16'],
        ];
    }
}
