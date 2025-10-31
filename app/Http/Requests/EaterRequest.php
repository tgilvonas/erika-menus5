<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EaterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'=>'required',
            'diet_type_id'=>'required|numeric'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'=>__('general.required'),
            'diet_type_id.required'=>__('general.you_must_select'),
            'diet_type_id.numeric'=>__('general.you_must_select'),
        ];
    }

}
