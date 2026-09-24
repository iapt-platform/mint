<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/progress` 的入参。
 *
 * 原来有个 `view` 参数，但它只有 `channel` 一个合法值，传别的直接 422——
 * 那不是业务路径开关，是噪音，已去掉（硬规范第 1 条）。
 */
class IndexProgressRequest extends FormRequest
{
    /**
     * 排序可用的列。**必须白名单**：原来是 `'progress_chapters.'.$request->input('order')`
     * 直接拼进 orderBy，传个不存在的列名就是 QueryException → 500。
     *
     * @var list<string>
     */
    public const SORTABLE = [
        'book', 'para', 'lang', 'progress', 'title',
        'last_chapter_completed_at', 'completed_at', 'updated_at',
    ];

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
            // channel uid 列表，**下划线分隔**（不是逗号）——既有契约，不要改
            'channels' => ['required', 'string'],
            'level' => ['sometimes', 'integer', 'min:1'],
            'lang' => ['sometimes', 'string', 'max:20'],
            'book' => ['sometimes', 'integer', 'min:1'],
            'order' => ['sometimes', 'string', Rule::in(self::SORTABLE)],
            'dir' => ['sometimes', 'string', 'in:asc,desc'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
