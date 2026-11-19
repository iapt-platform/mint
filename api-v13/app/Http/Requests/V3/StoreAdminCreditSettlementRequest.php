<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/admin/credit-settlements` 的入参：手工跑一次积分结算。
 */
class StoreAdminCreditSettlementRequest extends FormRequest
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
            'date' => [
                'sometimes', 'date_format:Y-m-d',
                'after_or_equal:'.config('mint.credits.earn_since'),
                'before_or_equal:'.now('UTC')->toDateString(),
            ],
        ];
    }
}
