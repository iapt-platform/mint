<?php

namespace App\Http\Requests\V3;

use App\Models\DhammaTerm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 新建术语（`POST /v3/channels/{channel}/terms`、`POST /v3/studios/{studio}/terms`）。
 *
 * studio 级术语要给 language；channel 里的跟 channel 的语言，传了也不用。分类写在 note 的 {{category|…}} 里。
 */
class StoreTermRequest extends FormRequest
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
        $isStudio = $this->route('studio') !== null;

        return [
            'word' => ['required', 'string', 'max:1024'],
            'tag' => ['sometimes', 'nullable', 'string', 'max:1024', 'not_regex:/@/'],
            'meaning' => ['required', 'string', 'max:1024'],
            'other_meaning' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'note' => ['sometimes', 'nullable', 'string'],
            'summary' => ['sometimes', 'nullable', 'string'],
            'quality' => ['sometimes', 'nullable', 'string', Rule::in(DhammaTerm::QUALITIES)],
            'content_type' => ['sometimes', 'string', Rule::in(DhammaTerm::CONTENT_TYPES)],
            'redirect' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'language' => [$isStudio ? 'required' : 'prohibited', 'string', 'max:16'],
        ];
    }
}
