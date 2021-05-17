<?php

namespace Mint\Service\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LicenseRequest extends FormRequest
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
            'access_code' => 'required',
            'envato_email' => 'required|email'
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
            'access_code' => trans('setup.license.props.access_code'),
            'envato_email' => trans('setup.license.props.envato_email')
        ];
    }
}
