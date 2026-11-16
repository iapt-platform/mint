<?php

namespace App\Http\Requests\V3;

use App\Models\Channel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * `PATCH /v3/channels/{channel}` 的入参：改 channel 信息。只做局部更新，没有必填项。
 */
class UpdateChannelRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:128'],
            'type' => ['sometimes', 'string', Rule::in(IndexChannelRequest::TYPES)],
            'lang' => ['sometimes', 'string', 'max:16'],
            'summary' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'status' => ['sometimes', 'integer', Rule::in([Channel::STATUS_PRIVATE, Channel::STATUS_UNLISTED, Channel::STATUS_PUBLIC])],
            'source_type' => ['sometimes', 'string', Rule::in(['original', 'reprint', 'ai'])],
            'source_id' => ['sometimes', 'nullable', 'string', 'max:36'],
            'config' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
