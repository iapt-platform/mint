<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-related-paragraphs/aggregate` 的入参：列出所有 SC 缩写。
 *
 * 过滤器沿用 wikipali 的坐标命名：`file` 是典籍文件号（表里的 `book` 列，
 * 1-217），`book` 是真书号（表里的 `book_id` 列）。其余参数是可选收窄过滤器；
 * 分页走 Laravel 默认的 page / per_page。
 */
class AggregateTipitakaRelatedParagraphRequest extends FormRequest
{
    /**
     * 公共只读端点，不需要登录。
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
            'file' => ['integer', 'min:1'],
            'book' => ['integer', 'min:1'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
