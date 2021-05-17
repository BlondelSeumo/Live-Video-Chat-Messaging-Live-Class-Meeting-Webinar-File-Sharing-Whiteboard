<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConnectRequest extends FormRequest
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
            'email' => 'required|email',
            'name'  => 'required|min:3|max:50',
            'code'  => 'sometimes|required|integer|min:6|max:6'
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
            'email' => __('auth.register.props.email'),
            'name'  => __('auth.register.props.name'),
            'code'  => __('auth.register.props.code')
        ];
    }
}