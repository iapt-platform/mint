<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-reading/{channel}` 的入参。
 *
 * **只有 channel 是必填的，而且它在路径上。** 这里全是可选的过滤与分页参数，
 * 所以不带任何查询串就能出结果——那就是「取这个 channel 的全部译文」，
 * 下载场景要的正是这个。
 *
 * `book` 只在跟 `chapter` / `para` 一起用时才必填：那两个是书内的段落号，
 * 没有 book 就没有意义。这是唯一一条参数间依赖，不是 v2 那种 `view=` 开关。
 */
class TipitakaReadingRequest extends FormRequest
{
    /**
     * 公开的阅读接口，不需要登录。
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
            'book' => ['integer', 'min:1', 'required_with:chapter,para,to'],
            // 章节起始段落号；服务端按 pali_texts 的 chapter_len 展开成段落区间
            'chapter' => ['integer', 'min:1', 'prohibits:para,to'],
            'para' => ['integer', 'min:1'],
            'to' => ['integer', 'min:1'],
            // 游标，形如 "9002-15"（book-para）。由 meta.next_cursor 给出，
            // 客户端原样回传即可，不要自己拼
            'after' => ['string', 'regex:/^\d+-\d+$/'],
            'page_size' => ['integer', 'min:1'],
            'unit' => ['string', 'in:para,byte'],
            'format' => ['string', 'in:html,markdown,react,text'],
            'include' => ['string', 'in:display,sentences,all'],
        ];
    }
}
