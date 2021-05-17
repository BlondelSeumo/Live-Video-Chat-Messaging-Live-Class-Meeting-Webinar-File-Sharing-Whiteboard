<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'email'                     => 'required|email',
            'code'                      => 'required',
            'new_password'              => 'required|min:6',
            'new_password_confirmation' => 'required|same:new_password'
        ];
    }

    /**
     * Translate fields with user friendly name.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'email'                     => __('auth.password.props.email'),
            'code'                      => __('auth.password.props.email'),
            'new_password'              => __('auth.password.props.new_password'),
            'new_password_confirmation' => __('auth.password.props.new_password_confirmation'),
        ];
    }
}
