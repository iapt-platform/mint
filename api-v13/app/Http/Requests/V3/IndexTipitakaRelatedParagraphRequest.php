<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-related-paragraphs` 的入参：查一个段落的关联段落。
 *
 * 两种互斥的入口，必须二选一：
 *   - 原功能：`book` + `para`，先定位锚点 (book_name, cs_para) 再查关联；
 *   - 新过滤器：直接给 `book_name` + `cs_para` 查关联。
 *
 * 这是参数间依赖，用 FormRequest 表达，不是 v2 那种 `view=` 开关。
 */
class IndexTipitakaRelatedParagraphRequest extends FormRequest
{
    /**
     * 公共只读端点，不需要登录。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'book' => ['integer', 'min:1', 'required_without:book_name', 'required_with:para', 'prohibits:book_name,cs_para'],
            'para' => ['integer', 'min:1', 'required_with:book'],
            'book_name' => ['string', 'required_without:book', 'required_with:cs_para', 'prohibits:book,para'],
            'cs_para' => ['integer', 'min:1', 'required_with:book_name'],
            'book_id' => ['integer', 'min:1'],
        ];
    }
}
