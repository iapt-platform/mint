<?php

namespace App\Http\Requests\V3;

use App\Models\Reaction;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/reactions/tally` 的入参：某个 target 的各 type 计数摘要。
 *
 * 聚合结果每个 type 一行，总行数不超过 {@see Reaction::TYPES} 的长度，
 * 所以不分页，也就没有 page / per_page。
 */
class IndexReactionTallyRequest extends FormRequest
{
    /**
     * 公共只读端点，不需要鉴权；登录时才额外富化 selected / id。
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
        ];
    }
}
