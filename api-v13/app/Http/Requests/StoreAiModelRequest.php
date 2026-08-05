<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiModelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // 真正的鉴权在 AiModelController::store()：需要先把 studio_name 解成 studio uid
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * 长度上限对齐 ai_models 表的列定义。
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:64'],
            'studio_name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'system_prompt' => ['nullable', 'string'],
            'url' => ['nullable', 'string', 'max:1024'],
            'model' => ['nullable', 'string', 'max:1024'],
            'key' => ['nullable', 'string', 'max:1024'],
            'privacy' => ['nullable', 'string', 'in:private,public,disable'],
        ];
    }
}
