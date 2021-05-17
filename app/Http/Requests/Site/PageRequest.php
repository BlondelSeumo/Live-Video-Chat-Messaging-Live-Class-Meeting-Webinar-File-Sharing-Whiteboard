<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;

class PageRequest extends FormRequest
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
            'title'    => 'required',
            'slug'     => 'required',
            'body'     => 'required',
            'template' => 'required',
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
            'title'    => __('site.page.props.title'),
            'slug'     => __('site.page.props.slug'),
            'body'     => __('site.page.props.body'),
            'template' => __('site.page_template.template')
        ];
    }
}
