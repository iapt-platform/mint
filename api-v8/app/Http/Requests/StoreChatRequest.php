<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'user_id' => 'required|string|exists:user_infos,userid'
        ];
    }

    public function messages()
    {
        return [
            'title.required' => '聊天标题不能为空',
            'title.max' => '聊天标题不能超过255个字符',
            'user_id.exists' => '用户不存在'
        ];
    }
}
