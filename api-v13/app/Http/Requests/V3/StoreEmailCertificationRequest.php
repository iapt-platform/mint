<?php

namespace App\Http\Requests\V3;

use App\Services\V3\SignUpService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/email-certifications` 的入参：给待注册的邮箱发验证码。
 */
class StoreEmailCertificationRequest extends FormRequest
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
            // user_infos.email 是 varchar(256)
            'email' => [
                'required', 'email', 'max:256',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (app(SignUpService::class)->emailRegistered((string) $value)) {
                        $fail(__('messages.email_registered'));
                    }
                },
            ],
            // 邮件语言，没有对应模板的回落 en，所以这里不限定取值
            'lang' => ['sometimes', 'string', 'max:16'],
        ];
    }
}
