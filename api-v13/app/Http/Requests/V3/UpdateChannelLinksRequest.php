<?php

namespace App\Http\Requests\V3;

use App\Models\ChannelLink;
use App\Services\V3\ChannelLinkService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `PATCH /v3/channels/{channel}/links` 的入参：传了哪个键就整体替换哪一种关系，没有必填项。
 *
 * 目标是否存在、看不看得见、是不是自己、逐词类型对不对，在 {@see ChannelLinkService} 里判。
 */
class UpdateChannelLinksRequest extends FormRequest
{
    /** 登录与权限在中间件 / Service 里判 */
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
            'derived_from' => ['sometimes', 'nullable', 'uuid'],
            'wbw' => ['sometimes', 'nullable', 'uuid'],
            'fallback' => ['sometimes', 'array', 'max:'.ChannelLink::LISTS[ChannelLink::FALLBACK]],
            'fallback.*' => ['uuid', 'distinct'],
            'term_source' => ['sometimes', 'array', 'max:'.ChannelLink::LISTS[ChannelLink::TERM_SOURCE]],
            'term_source.*' => ['uuid', 'distinct'],
        ];
    }
}
