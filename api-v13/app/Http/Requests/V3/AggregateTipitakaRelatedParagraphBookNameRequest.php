<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-related-paragraphs/aggregate/{book_name}` 的入参：
 * 列出该 SC 缩写下的 cs_para。book_name 在路径上。
 *
 * 其余参数是可选收窄过滤器；分页走 Laravel 默认的 page / per_page。
 */
class AggregateTipitakaRelatedParagraphBookNameRequest extends FormRequest
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
            'book' => ['integer', 'min:1'],
            'book_id' => ['integer', 'min:1'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
