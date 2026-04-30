<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Api\AuthApi;
use Illuminate\Support\Facades\Log;

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
        $user = AuthApi::current($this);
        if (!$user) {
            Log::warning('discussion store auth failed', ['request' => $this]);
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
