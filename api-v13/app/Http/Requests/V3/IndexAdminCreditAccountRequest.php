<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/admin/credit-accounts` 的入参：有积分流水的账号一览。
 */
class IndexAdminCreditAccountRequest extends FormRequest
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
            'filter.account_type' => ['sometimes', 'in:user,guest'],
            'filter.q' => ['sometimes', 'string', 'max:100'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}
