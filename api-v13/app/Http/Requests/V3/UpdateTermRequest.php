<?php

namespace App\Http\Requests\V3;

use App\Models\DhammaTerm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `PATCH /v3/terms/{term}`：只改传了的字段。word / tag 建立后不准改（引用方写 `[[word@tag]]`），
 * 要改就新建一条、把旧的 redirect 过去。乐观锁的 If-Match 在 Service 里判。
 */
class UpdateTermRequest extends FormRequest
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
            'word' => ['prohibited'],
            'tag' => ['prohibited'],
            'meaning' => ['sometimes', 'string', 'max:1024'],
            'other_meaning' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'note' => ['sometimes', 'nullable', 'string'],
            'summary' => ['sometimes', 'nullable', 'string'],
            'quality' => ['sometimes', 'nullable', 'string', Rule::in(DhammaTerm::QUALITIES)],
            'content_type' => ['sometimes', 'string', Rule::in(DhammaTerm::CONTENT_TYPES)],
            'redirect' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'language' => ['sometimes', 'string', 'max:16'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'word.prohibited' => __('messages.term_word_immutable'),
            'tag.prohibited' => __('messages.term_word_immutable'),
        ];
    }
}
