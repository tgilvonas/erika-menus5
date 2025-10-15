<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'proteins' => 'required|numeric',
            'fat' => 'required|numeric',
            'carbohydrates' => 'required|numeric',
            'calories' => 'required|numeric',
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
            'title.required' => __('general.required'),
            'proteins.required' => __('general.required'),
            'fat.required' => __('general.required'),
            'carbohydrates.required' => __('general.required'),
            'calories.required' => __('general.required'),
            'proteins.numeric' => __('general.must_be_numeric'),
            'fat.numeric' => __('general.must_be_numeric'),
            'carbohydrates.numeric' => __('general.must_be_numeric'),
            'calories.numeric' => __('general.must_be_numeric'),
        ];
    }
}
