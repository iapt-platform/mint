<?php

namespace App\Http\Requests\V3;

use App\Models\DhammaTerm;
use App\Services\V3\TermService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 术语列表（某个 channel / studio 的）的过滤与分页。都是可选叠加的过滤。
 */
class IndexTermRequest extends FormRequest
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
            'search' => ['sometimes', 'nullable', 'string', 'max:128'],
            'quality' => ['sometimes', 'string', Rule::in(DhammaTerm::QUALITIES)],
            'category' => ['sometimes', 'string', 'max:128'],
            // 查询串里是字符串：Laravel 的 boolean 规则不收 "true"/"false"，取值处用 \$request->boolean()
            'general' => ['sometimes', 'in:true,false,1,0'],
            'order' => ['sometimes', 'string', Rule::in(TermService::ORDERS)],
            'dir' => ['sometimes', 'in:asc,desc'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
