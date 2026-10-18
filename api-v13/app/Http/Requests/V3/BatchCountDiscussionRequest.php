<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/discussions/batch-count` 的入参：一批讨论对象（句子 / 逐词）。
 *
 * 用 POST 是因为一页可能有几百个对象，uuid 放查询串会超长。上限写死，免得一次请求拖垮库。
 */
class BatchCountDiscussionRequest extends FormRequest
{
    public const MAX_TARGETS = 500;

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
            'targets' => ['required', 'array', 'min:1', 'max:'.self::MAX_TARGETS],
            'targets.*.type' => ['required', 'string', Rule::in(['sentence', 'wbw'])],
            'targets.*.id' => ['required', 'uuid'],
        ];
    }
}
