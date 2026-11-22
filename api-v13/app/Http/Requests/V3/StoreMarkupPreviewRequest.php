<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/markup-previews`：要预览的源码与渲染口径。
 */
class StoreMarkupPreviewRequest extends FormRequest
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
            'content' => ['required', 'string', 'max:20000'],
            'content_type' => ['sometimes', 'string', 'in:markdown,markdown-e2'],
            'channels' => ['sometimes', 'array', 'max:10'],
            'channels.*' => ['uuid'],
            'lang' => ['sometimes', 'string', 'max:16', 'regex:/^[A-Za-z]{2,3}(-[A-Za-z0-9]{2,8})*$/'],
        ];
    }
}
