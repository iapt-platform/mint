<?php

namespace App\Http\Requests\V3;

use App\Models\Reaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/reactions` 的入参：某个 target 下的公共 reaction 列表。
 *
 * `target_id` 必填不是随口加的：likes 表没有全表可读的用例，
 * 不带条件就能拉全表的列表端点是设计错误。
 */
class IndexReactionRequest extends FormRequest
{
    /**
     * 公共只读端点，不需要鉴权。
     */
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
            'target_id' => ['required', 'uuid'],
            'type' => ['nullable', 'string', Rule::in(Reaction::TYPES)],
            'page' => ['integer', 'min:1'],
            // 上限 200 与 v3 其它列表一致：没有上限时 ?per_page=100000 能一次拉空一个 target
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
