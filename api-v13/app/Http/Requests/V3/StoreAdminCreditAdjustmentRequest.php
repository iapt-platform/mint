<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/admin/credit-adjustments` 的入参：管理员给登录用户加减积分。
 */
class StoreAdminCreditAdjustmentRequest extends FormRequest
{
    public const MAX_DELTA = 1_000_000;

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
            'user' => ['required', 'string', 'max:255'],
            'delta' => ['required', 'integer', 'not_in:0', 'between:-'.self::MAX_DELTA.','.self::MAX_DELTA],
            'note' => ['required', 'string', 'max:255'],
            'ref' => ['sometimes', 'uuid'],
        ];
    }
}
