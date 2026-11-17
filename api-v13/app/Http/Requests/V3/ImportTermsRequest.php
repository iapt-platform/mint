<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 术语导入：上传一个 xlsx（multipart/form-data 的 `file`），不超过 5MB。
 */
class ImportTermsRequest extends FormRequest
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
            'file' => ['required', 'file', 'extensions:xlsx', 'max:5120'],
        ];
    }
}
