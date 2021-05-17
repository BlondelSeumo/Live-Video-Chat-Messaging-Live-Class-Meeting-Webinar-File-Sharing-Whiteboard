<?php

namespace Mint\Service\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallRequest extends FormRequest
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
        $option = request()->query('option');

        $rules = array();

        $rules = [
            'db_port'     => 'required',
            'db_host'     => 'required',
            'db_database' => 'required',
            'db_username' => 'required'
        ];

        if ($option === 'admin') {
            $rules['name']                  = 'required';
            $rules['email']                 = 'required|email';
            $rules['username']              = 'required';
            $rules['password']              = 'required|min:6';
            $rules['password_confirmation'] = 'required|same:password';
        }

        if ($option === 'access_code') {
            $rules['access_code']  = 'required';
            $rules['envato_email'] = 'required|email';
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
            'db_port'               => trans('setup.install.props.db_port'),
            'db_host'               => trans('setup.install.props.db_host'),
            'db_database'           => trans('setup.install.props.db_database'),
            'db_username'           => trans('setup.install.props.db_username'),
            'name'                  => trans('setup.install.props.name'),
            'email'                 => trans('setup.install.props.email'),
            'username'              => trans('setup.install.props.username'),
            'password'              => trans('setup.install.props.password'),
            'password_confirmation' => trans('setup.install.props.password_confirmation'),
            'access_code'           => trans('setup.install.props.access_code'),
            'envato_email'          => trans('setup.install.props.envato_email')
        ];
    }
}
