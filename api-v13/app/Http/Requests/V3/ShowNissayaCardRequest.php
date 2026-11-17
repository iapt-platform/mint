<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/nissaya-cards/{ending}`：卡片的语言（可选，缺省用请求协商出的界面语言）。
 */
class ShowNissayaCardRequest extends FormRequest
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
            'lang' => ['sometimes', 'string', 'max:16', 'regex:/^[A-Za-z]{2,3}(-[A-Za-z0-9]{2,8})*$/'],
        ];
    }
}
