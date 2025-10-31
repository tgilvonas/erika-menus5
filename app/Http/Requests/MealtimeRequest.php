<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MealtimeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            'title'=>'required|max:255',
            'percent_from'=>'required|numeric',
            'percent_to'=>'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            'title.required'=>__('general.required'),
            'percent_from.required'=>__('general.required'),
            'percent_from.numeric'=>__('general.must_be_numeric'),
            'percent_to.required'=>__('general.required'),
            'percent_to.numeric'=>__('general.must_be_numeric'),
        ];
    }
}
