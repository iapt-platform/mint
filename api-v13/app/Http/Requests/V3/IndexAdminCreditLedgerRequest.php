<?php

namespace App\Http\Requests\V3;

use App\Models\CreditLedgerEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/admin/credit-ledger` 的入参：积分流水。按账号查时 account_type 与 account_id 必须一起给
 * （设备 id 由客户端填，可能与某个用户 uid 相同，只按 id 查会把两种账号混在一起）。
 */
class IndexAdminCreditLedgerRequest extends FormRequest
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
            'filter.account_type' => ['required_with:filter.account_id', 'in:user,guest'],
            'filter.account_id' => ['sometimes', 'uuid'],
            'filter.kind' => ['sometimes', Rule::in(CreditLedgerEntry::KINDS)],
            'filter.bucket' => ['sometimes', Rule::in(CreditLedgerEntry::BUCKETS)],
            'filter.source' => ['sometimes', 'in:reading,editing,agent_run,admin'],
            'filter.from' => ['sometimes', 'date_format:Y-m-d'],
            'filter.to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:filter.from'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}
