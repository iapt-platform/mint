<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\AuthService;

class StoreDiscussionRequest extends FormRequest
{
    private $user;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = AuthService::current($this);
        if (!$user) {
            return false;
        }
        $this->user = $user;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'res_id' => 'required|string',
            'res_type' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'res_id.required' => 'res_type是必填项',
            'res_type.required' => 'type是必填项',
        ];
    }
}
