<?php

namespace App\Http\Requests\V3;

use App\Models\Discussion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/tipitaka-discussions/{channel}` 的入参。
 *
 * **只有 channel 是必填的，而且它在路径上。** 这里全是可选的范围、过滤与分页参数：
 * 不带 book 就是整个 channel；`book` 只在跟 `chapter` / `para` 一起用时才必填
 * （与 `tipitaka-reading` 同一口径）。
 */
class IndexTipitakaDiscussionRequest extends FormRequest
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
            'book' => ['integer', 'min:1', 'required_with:chapter,para,to'],
            // 章节起始段落号；服务端按 pali_texts 的 chapter_len 展开成段落区间
            'chapter' => ['integer', 'min:1', 'prohibits:para,to'],
            'para' => ['integer', 'min:1'],
            'to' => ['integer', 'min:1'],
            // 逗号分隔多选，如 issue,glossary；不传只列话题（评论线程）
            'type' => ['sometimes', 'string', 'regex:/^[a-z]+(,[a-z]+)*$/', function (string $attribute, mixed $value, Closure $fail) {
                if (array_diff(explode(',', (string) $value), Discussion::COMMENT_TYPES) !== []) {
                    $fail(__('validation.in', ['attribute' => $attribute]));
                }
            }],
            'status' => ['sometimes', 'string', Rule::in(Discussion::STATUSES)],
            // 逗号分隔多选，如 error,warning
            'severity' => ['sometimes', 'string', 'regex:/^[a-z]+(,[a-z]+)*$/'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
