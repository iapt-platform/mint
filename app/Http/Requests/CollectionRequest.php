<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CollectionRequest extends FormRequest
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
            /*
            string Id        = 1;
            string Title     = 2;
            string Subtitle  = 3;
            string Summary   = 4;
            string ArticleList   = 5;
            repeated Tag Tags = 6;

            string Lang = 51;
            string EditorId = 52;
            EnumPublicity Publicity = 53;
             */
            'id' => 'required|unique:posts',
            'title' => 'required|max:512',
            'subtitle' => 'nullable|max:512',
        ];
    }
}
