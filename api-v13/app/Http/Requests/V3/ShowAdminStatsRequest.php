<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * 管理后台统计（`/v3/admin/reading-stats`、`/v3/admin/agent-stats`）的共同入参：
 * UTC 日的闭区间 [from, to]，缺省近 30 天，最长 366 天。
 */
class ShowAdminStatsRequest extends FormRequest
{
    public const MAX_DAYS = 366;

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
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
            'client' => ['sometimes', 'in:all,web,mobile'],
            'account_type' => ['sometimes', 'in:all,user,guest'],
        ];
    }
}
