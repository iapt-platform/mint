<?php

namespace App\Http\Requests\V3;

use App\Models\AgentRun;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/admin/agent-runs` 的入参：管理员查阅提问列表。
 */
class IndexAdminAgentRunRequest extends FormRequest
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
            'filter.status' => ['sometimes', Rule::in(AgentRun::STATUSES)],
            'filter.route' => ['sometimes', Rule::in(AgentRun::ROUTES)],
            'filter.account_type' => ['sometimes', 'in:user,guest'],
            'filter.client' => ['sometimes', 'in:mobile,web'],
            'filter.improve' => ['sometimes', 'in:true,false,1,0'],
            'filter.q' => ['sometimes', 'string', 'max:200'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}
