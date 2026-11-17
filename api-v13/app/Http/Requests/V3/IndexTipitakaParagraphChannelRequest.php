<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-paragraph-channels/{book}/{para}` 的入参：只有分页。
 */
class IndexTipitakaParagraphChannelRequest extends FormRequest
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
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
