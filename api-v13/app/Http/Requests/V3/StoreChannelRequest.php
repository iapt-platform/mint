<?php

namespace App\Http\Requests\V3;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `POST /v3/studios/{studio}/channels` 的入参：建 channel。同一 studio 内不能重名（Service 里判）。
 */
class StoreChannelRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:128'],
            'type' => ['required', 'string', Rule::in(IndexChannelRequest::TYPES)],
            'lang' => ['required', 'string', 'max:16'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'status' => ['sometimes', 'integer', Rule::in([Channel::STATUS_PRIVATE, Channel::STATUS_UNLISTED, Channel::STATUS_PUBLIC])],
            'source_type' => ['sometimes', 'string', Rule::in(['original', 'reprint', 'ai'])],
            'source_id' => ['sometimes', 'nullable', 'string', 'max:36'],
        ];
    }
}
