<?php

namespace App\Http\Requests\V3;

use App\Models\Discussion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `PATCH /v3/discussions/{discussion}` 的入参：局部更新，只改传了的字段。
 *
 * title / status / type / severity 只对话题有效，回复传了 422——这一条与
 * severity 是否匹配最终 type，都要看库里那条记录，在 Service 里判。
 * 批注相关的规则（锚点只有批注收、commentary 正文格式、不能与评论线程互转、没有 status）
 * 同样取决于那条记录的类型，也在 Service 里判。
 */
class UpdateDiscussionRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'max:256'],
            'content' => ['sometimes', 'nullable', 'string', 'max:20000'],
            'status' => ['sometimes', 'string', Rule::in(Discussion::STATUSES)],
            'type' => ['sometimes', 'string', Rule::in(Discussion::COMMENT_TYPES)],
            'severity' => ['sometimes', 'nullable', 'string'],
            'pos_start' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'pos_end' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'quote_exact' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'quote_prefix' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'quote_suffix' => ['sometimes', 'nullable', 'string', 'max:1024'],
        ];
    }
}
