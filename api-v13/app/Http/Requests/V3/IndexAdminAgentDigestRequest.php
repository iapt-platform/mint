<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/admin/agent-runs/digest` 的入参：分析用的精简导出。
 */
class IndexAdminAgentDigestRequest extends FormRequest
{
    public const MAX_LIMIT = 2000;

    /** 带回答与证据全文时每条可达几万字，上限收紧 */
    public const MAX_LIMIT_FULL = 200;

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
            'full' => ['sometimes', 'boolean'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:'.($this->boolean('full') ? self::MAX_LIMIT_FULL : self::MAX_LIMIT)],
        ];
    }
}
