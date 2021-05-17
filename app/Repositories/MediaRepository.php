<?php
namespace App\Repositories;

use App\Helpers\ArrHelper;
use App\Helpers\SysHelper;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class MediaRepository
{
    protected $default_allowed_max_file_count = 10;
    protected $default_allowed_max_file_size = 1024 * 1024 * 10;
    protected $default_allowed_file_extensions = ["jpg","png","jpeg","pdf","doc","docx","xls","xlsx","txt"];
    protected $temp_path = 'temp/';

    /**
     * Get pre requisite for media upload
     */
    public function getPreRequisite() : array
    {
        $medias = ArrHelper::getVar('media');

        $post_max_size = SysHelper::getPostMaxSize();

        if (! request('module')) {
            $allowed_file_extensions = $this->default_allowed_file_extensions;
            $allowed_max_file_count = $this->default_allowed_max_file_count;
            return compact('allowed_file_extensions', 'allowed_max_file_count', 'post_max_size');
        }

        $media = Arr::get($medias, request('module'), []);
        
        $allowed_file_extensions = Arr::get($media, 'allowed_file_extensions', $this->default_allowed_file_extensions);
        $allowed_max_file_count = Arr::get($media, 'allowed_max_file_count', $this->default_allowed_max_file_count);

        return compact('allowed_file_extensions', 'allowed_max_file_count', 'post_max_size');
    }

    /**
     * Upload new file
     */
    public function upload()
    {
        if (! request('module') || ! request('token')) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }

        $pre_requisites = $this->getPreRequisite();

        $allowed_file_extensions = Arr::get($pre_requisites, 'allowed_file_extensions');
        $allowed_max_file_count  = Arr::get($pre_requisites, 'allowed_max_file_count');

        // if ($auth_required && \Auth::check()) {
        //     throw new AuthenticationException(__('auth.token_not_provided'));
        // }

        $extension = request()->file('file')->extension();

        if (! in_array($extension, $allowed_file_extensions)) {
            throw ValidationException::withMessages(['message' => __('upload.invalid_extension', ['attribute' => $extension ? : __('upload.selected_extension')])]);
        }

        $existing_uploaded_file_count = count(\Storage::allFiles($this->temp.request('module').'/'.request('token')));

        if ($existing_uploaded_file_count >= $allowed_max_file_count) {
            throw ValidationException::withMessages(['message' => __('upload.max_file_limit_crossed', ['number' => $allowed_max_file_count])]);
        }

        $filename = request()->file('file')->store('uploads/'.$module);

        $options['mime'] = request('mime');

        $upload = $this->upload->forceCreate([
            'uploadable_type' => $module,
            'uploadable_id' => request('module_id') ? : null,
            'upload_token' => $token,
            'user_filename' => request()->file('file')->getClientOriginalName(),
            'filename' => $filename,
            'uuid' => Str::uuid(),
            'user_id' => null,
            'options' => $options
        ]);

        return $upload;
    }
}
