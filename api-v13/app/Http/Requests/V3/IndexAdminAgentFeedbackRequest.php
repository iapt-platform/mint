<?php

namespace App\Http\Requests\V3;

use App\Models\AgentFeedback;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/admin/agent-feedback` 的入参：管理员审阅用户评价。
 */
class IndexAdminAgentFeedbackRequest extends FormRequest
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
            'filter.rating' => ['sometimes', 'string', 'in:1,-1'],
            'filter.tag' => ['sometimes', Rule::in([...AgentFeedback::UP_TAGS, ...AgentFeedback::DOWN_REASONS])],
            'filter.has_comment' => ['sometimes', 'in:true,false,1,0'],
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:100'],
        ];
    }
}
