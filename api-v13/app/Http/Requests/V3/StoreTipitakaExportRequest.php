<?php

namespace App\Http\Requests\V3;

use App\Services\V3\TipitakaExportService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 发起三藏下载。作品用 work（sn）或 book + para（该段所在的作品，给只认坐标的 mobile 用）指定；
 * 与 category 三选一，都不给 = 整个 channel。
 */
class StoreTipitakaExportRequest extends FormRequest
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
            'channel' => ['required', 'uuid'],
            'work' => ['sometimes', 'integer', 'min:1', 'prohibits:category,book,para'],
            // 不加 sometimes：缺了另一半时 required_with 要能跑到
            'book' => ['integer', 'min:1', 'required_with:para', 'prohibits:category'],
            'para' => ['integer', 'min:1', 'required_with:book'],
            'category' => ['sometimes', 'integer', 'min:1'],
            'format' => ['required', 'string', Rule::in(TipitakaExportService::FORMATS)],
        ];
    }
}
