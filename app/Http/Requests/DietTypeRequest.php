<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DietTypeRequest extends FormRequest
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
            'title'=>'required',
            'proteins_min'=>'required|numeric',
            'proteins_max'=>'required|numeric',
            'fat_min'=>'required|numeric',
            'fat_max'=>'required|numeric',
            'carbohydrates_min'=>'required|numeric',
            'carbohydrates_max'=>'required|numeric',
            'calories_min'=>'required|numeric',
            'calories_max'=>'required|numeric'
        ];
    }

    /**
     * Get custom messages for validator errors.
     * @return array
     */
    public function messages()
    {
        return [
            'title.required'=>__('general.required'),

            'proteins_min.required'=>__('general.required'),
            'proteins_min.numeric'=>__('general.must_be_numeric'),
            'proteins_max.required'=>__('general.required'),
            'proteins_max.numeric'=>__('general.must_be_numeric'),

            'fat_min.required'=>__('general.required'),
            'fat_min.numeric'=>__('general.must_be_numeric'),
            'fat_max.required'=>__('general.required'),
            'fat_max.numeric'=>__('general.must_be_numeric'),

            'carbohydrates_min.required'=>__('general.required'),
            'carbohydrates_min.numeric'=>__('general.must_be_numeric'),
            'carbohydrates_max.required'=>__('general.required'),
            'carbohydrates_max.numeric'=>__('general.must_be_numeric'),

            'calories_min.required'=>__('general.required'),
            'calories_min.numeric'=>__('general.must_be_numeric'),
            'calories_max.required'=>__('general.required'),
            'calories_max.numeric'=>__('general.must_be_numeric'),
        ];
    }
}
