<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'sometimes|required',
            'new_password'     => 'required|confirmed|min:6|different:current_password|same:new_password_confirmation'
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
            'current_password'          => __('auth.password.props.current_password'),
            'new_password'              => __('auth.password.props.new_password'),
            'new_password_confirmation' => __('auth.password.props.new_password_confirmation'),
        ];
    }
}
