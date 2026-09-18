<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() && $this->user()->roles === 'ROLE_SUPERADMIN';
    }

    public function rules()
    {
        $emailRule = Rule::unique('users', 'email');

        if (is_numeric($this->id)) {
            $emailRule = $emailRule->ignore($this->id);
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailRule],
            'roles' => ['required', 'string', Rule::in(['ROLE_SUPERADMIN', 'ROLE_DIETOLOGIST'])],
            'password' => [is_numeric($this->id) ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('general.required'),
            'email.required' => __('general.required'),
            'email.email' => __('validation.email'),
            'email.unique' => __('general.already_exists'),
            'roles.required' => __('general.required'),
            'roles.in' => __('general.required'),
            'password.required' => __('general.required'),
            'password.min' => __('general.password_strength_description'),
            'password.confirmed' => __('validation.confirmed'),
        ];
    }
}
