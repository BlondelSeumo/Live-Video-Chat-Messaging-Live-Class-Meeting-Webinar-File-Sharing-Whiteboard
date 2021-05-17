<?php

namespace App\Traits;

use App\Helpers\ArrHelper;
use App\Helpers\SysHelper;
use Illuminate\Support\Arr;

trait CustomMedia
{
    protected $default_allowed_max_file_count = 10;
    protected $default_allowed_max_file_size = 1024 * 1024 * 10;
    protected $default_allowed_file_extensions = ["jpg","png","jpeg","pdf","doc","docx","xls","xlsx","txt"];
    protected $temp_path = 'temp/';

    /**
     * Get pre requisite
     */
    public function mediaPreRequisite() : array
    {
        $medias = ArrHelper::getVar('media');

        $post_max_size = SysHelper::getPostMaxSize();

        if (! request()->query('module')) {
            $allowed_file_extensions = $this->default_allowed_file_extensions;
            $allowed_max_file_count = $this->default_allowed_max_file_count;
            return compact('allowed_file_extensions', 'allowed_max_file_count', 'post_max_size');
        }

        $media = Arr::get($medias, request()->query('module'), []);
        
        $allowed_file_extensions = Arr::get($media, 'allowed_file_extensions', $this->default_allowed_file_extensions);
        $allowed_max_file_count = Arr::get($media, 'allowed_max_file_count', $this->default_allowed_max_file_count);

        return compact('allowed_file_extensions', 'allowed_max_file_count', 'post_max_size');
    }
}