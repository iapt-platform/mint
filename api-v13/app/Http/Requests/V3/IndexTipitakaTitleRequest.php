<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-chapters` 的入参。
 *
 * 全是过滤条件，但至少要给 `q` 或 `book` 之一：不加条件就是 3.7 万条标题全表，没人会翻。
 * 两者可以同时给（在一本书里按名字找）。`para` 是 book 里的一章，只列它下面的标题，所以要和 book 一起给。
 */
class IndexTipitakaChapterRequest extends FormRequest
{
    /** layer 的取值：根本典、义注、复注、其他 */
    public const LAYERS = ['mula', 'atthakatha', 'tika', 'anna'];

    /**
     * 公开的只读接口。
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
            'q' => ['required_without:book', 'string', 'min:1', 'max:200'],
            'book' => ['required_without:q', 'required_with:para', 'integer', 'min:1'],
            'para' => ['sometimes', 'integer', 'min:1'],
            'layer' => ['sometimes', 'string', 'regex:/^('.implode('|', self::LAYERS).')(,('.implode('|', self::LAYERS).'))*$/'],
            'max_level' => ['sometimes', 'integer', 'min:1', 'max:7'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
