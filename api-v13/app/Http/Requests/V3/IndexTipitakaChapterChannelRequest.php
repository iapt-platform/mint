<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/tipitaka-chapter-channels/{book}/{para}` 的入参。
 *
 * book / para 在路径上（路由约束保证是数字），这里只有分页。
 */
class IndexTipitakaChapterChannelRequest extends FormRequest
{
    /**
     * 公开的只读接口；可见性在 Service 里按登录用户判。
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
        ];
    }
}
