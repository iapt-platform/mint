<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'chat_id' => 'required|string|size:36',
            'messages' => 'required|array|min:1',
            'messages.*.parent_id' => 'nullable|string|exists:chat_messages,uid',
            'messages.*.session_id' => 'nullable|string|size:36',
            'messages.*.role' => ['required', Rule::in(['user', 'assistant', 'tool'])],
            'messages.*.content' => 'nullable|string',
            'messages.*.model_name' => 'nullable|string|max:100',
            'messages.*.tool_calls' => 'nullable|array',
            'messages.*.tool_calls.*.id' => 'required_with:tool_calls|string',
            'messages.*.tool_calls.*.function' => 'required_with:tool_calls|string',
            'messages.*.tool_calls.*.arguments' => 'required_with:tool_calls',
            'messages.*.tool_call_id' => 'nullable|string|max:100'
        ];
    }

    public function messages()
    {
        return [
            'messages.required' => '批量更新的消息列表不能为空',
            'messages.*.parent_id.required' => '消息ID不能为空',
            'messages.*.parent_id.exists' => '消息不存在'
        ];
    }
}
