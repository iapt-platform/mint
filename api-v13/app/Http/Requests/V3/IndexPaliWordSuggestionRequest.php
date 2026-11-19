<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/pali-word-suggestions`：巴利词的前缀补全。
 */
class IndexPaliWordSuggestionRequest extends FormRequest
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
            'q' => ['required', 'string', 'max:64'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }
}
