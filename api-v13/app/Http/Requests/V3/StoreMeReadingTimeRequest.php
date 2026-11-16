<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `POST /v3/me/reading-time` 的入参：一段读经时长。客户端每 5 分钟或切后台时报一次。
 */
class StoreMeReadingTimeRequest extends FormRequest
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
            'seconds' => ['required', 'integer', 'min:1', 'max:900'],
            'day' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
