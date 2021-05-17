<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
        $rules = [
            'profile.name'       => 'required|min:2',
            'email'              => array(
                'required',
                'email',
                Rule::unique('users')->ignore($this->route('user'))
            ),
            'username'              => array(
                'required',
                Rule::unique('users')->ignore($this->route('user'))
            ),
            'profile.birth_date' => 'sometimes|date',
            'profile.gender'     => 'required|array',
            'role' => 'required|array'
        ];

        if ($this->method() === 'POST' || ($this->method() === 'PATCH' && request()->boolean('force_password'))) {
            $rules['password'] = 'required|min:6|same:password_confirmation';
        }

        return $rules;
    }

    /**
     * Translate fields with user friendly name.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'profile.name'          => __('user.props.name'),
            'email'                 => __('user.props.email'),
            'username'              => __('user.props.username'),
            'password'              => __('user.props.password'),
            'password_confirmation' => __('user.props.password_confirmation'),
            'profile.birth_date'    => __('user.props.birth_date'),
            'profile.gender'        => __('user.props.gender')
        ];
    }
}
