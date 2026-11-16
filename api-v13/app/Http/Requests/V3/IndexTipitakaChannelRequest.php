<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/tipitaka-channels` 的入参：有译文的公开 channel 目录。
 */
class IndexTipitakaChannelRequest extends FormRequest
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
            'type' => ['sometimes', 'string', Rule::in(IndexChannelRequest::TYPES)],
            'lang' => ['sometimes', 'string', 'max:16'],
            'min_progress' => ['sometimes', 'numeric', 'min:0', 'max:1'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
