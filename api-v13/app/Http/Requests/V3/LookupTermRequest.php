<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/terms`：按词查。word 必填（逗号分隔，最多 50 个）。
 */
class LookupTermRequest extends FormRequest
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
            'word' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail) {
                if (count(array_filter(explode(',', (string) $value), fn ($w) => trim($w) !== '')) > 50) {
                    $fail(__('validation.max.array', ['attribute' => $attribute, 'max' => 50]));
                }
            }],
            'tag' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'channel' => ['sometimes', 'uuid'],
            'language' => ['sometimes', 'string', 'max:16'],
            // 查询串里是字符串：Laravel 的 boolean 规则不收 "true"/"false"，取值处用 \$request->boolean()
            'with_body' => ['sometimes', 'in:true,false,1,0'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
