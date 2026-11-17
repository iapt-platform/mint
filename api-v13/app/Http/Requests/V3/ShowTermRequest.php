<?php

namespace App\Http\Requests\V3;

use Illuminate\Foundation\Http\FormRequest;

/**
 * `GET /v3/terms/{term}`：正文按哪些 channel 渲染、read / edit 模式、节点树或 HTML（都可选）。
 */
class ShowTermRequest extends FormRequest
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
            'channels' => ['sometimes', 'string', 'max:1024', 'regex:/^[0-9a-fA-F-]{36}(,[0-9a-fA-F-]{36}){0,19}$/'],
            'mode' => ['sometimes', 'in:read,edit'],
            'format' => ['sometimes', 'in:tree,html'],
        ];
    }

    /**
     * @return array{channels?: list<string>, mode?: string, format?: string}
     */
    public function render(): array
    {
        $data = $this->validated();
        if (isset($data['channels'])) {
            $data['channels'] = array_values(array_unique(explode(',', $data['channels'])));
        }

        return $data;
    }
}
