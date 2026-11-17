<?php

namespace App\Http\Requests\V3;

use App\Services\V3\ChannelService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `GET /v3/studios/{studio}/channels` 的入参：某个 studio（本人或团队）的工作区列表。
 */
class IndexStudioChannelRequest extends FormRequest
{
    /**
     * 登录与权限在中间件 / Service 里判：authorize() 返回 false 会渲染成 403，
     * 而「没登录」应该是 401、无权要带具体文案。
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
            'relation' => ['sometimes', 'in:own,shared,all'],
            'collaborator' => ['sometimes', 'uuid'],
            'search' => ['sometimes', 'nullable', 'string', 'max:128'],
            'type' => ['sometimes', 'string', Rule::in(IndexChannelRequest::TYPES)],
            'lang' => ['sometimes', 'string', 'max:16'],
            // 不能用 sometimes：缺 book 时 sometimes 会跳过整条规则，required_with 就不生效
            'book' => ['required_with:para', 'integer', 'min:1'],
            'para' => ['required_with:book', 'integer', 'min:1'],
            'order' => ['sometimes', 'string', Rule::in(ChannelService::ORDERS)],
            'dir' => ['sometimes', 'in:asc,desc'],
            'page' => ['integer', 'min:1'],
            'per_page' => ['integer', 'min:1', 'max:200'],
        ];
    }
}
