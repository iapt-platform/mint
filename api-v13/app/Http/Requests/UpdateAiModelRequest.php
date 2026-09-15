<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 真正的鉴权在 AiModelController::update()：需要拿到 $aiModel 才能判 owner
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * 全部字段用 sometimes：update 是增量的，未提交的字段保持原值。
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:64'],
            'description' => ['sometimes', 'nullable', 'string'],
            'system_prompt' => ['sometimes', 'nullable', 'string'],
            'url' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'model' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'key' => ['sometimes', 'nullable', 'string', 'max:1024'],
            'privacy' => ['sometimes', 'required', 'string', 'in:private,public,disable'],
        ];
    }
}
