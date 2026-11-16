<?php

namespace App\Http\Requests\V3;

use App\Services\V3\ChannelProgressService;
use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/channels/batch-progress` 的入参：一批句子 id，可选只看某个 studio 名下的。
 */
class ChannelBatchProgressRequest extends FormRequest
{
    /**
     * 登录与权限在 Service 里判：未登录也能查（只看得到公开的）。
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
            'sentences' => ['required', 'array', 'min:1', 'max:'.ChannelProgressService::MAX_SENTENCES],
            'sentences.*' => ['string', 'regex:/^\d+-\d+-\d+-\d+$/'],
            'studio' => ['sometimes', 'uuid'],
        ];
    }
}
