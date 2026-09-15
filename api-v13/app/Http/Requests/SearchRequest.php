<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * 检索是公开接口，不需要鉴权。
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 空 key 必须拦在查询之前。它不会报错，而是悄悄匹配到「空值」那一类数据——
     * wbw 检索里 real 为空串的行有四百多万条、覆盖 49 万个段落，标题检索里
     * like '%%' 命中全部三万多条。调用方拿到的是一个成功的响应和满屏结果，
     * 却与查询无关；这比直接报错难发现得多。
     *
     * regex 要求 key 里至少有一个字符既不是空白也不是分隔符：各接口有的按逗号
     * 切词、有的按分号切，只由分隔符组成的 key（`,,`、`;;`）在任何一边都是空的。
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'key' => ['required', 'string', 'regex:/[^\s,;]/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'key.regex' => 'The key field must contain at least one searchable word.',
        ];
    }
}
