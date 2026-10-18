<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/discussions/{discussion}/replies` 的入参：回复只有正文。
 *
 * 类型、挂靠对象一律继承话题，不收客户端传的；也没有标题。
 */
class StoreDiscussionReplyRequest extends FormRequest
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
            'notify' => ['sometimes', 'boolean'],
        ];
    }
}
