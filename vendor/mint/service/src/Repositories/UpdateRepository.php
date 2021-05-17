<?php
namespace Mint\Service\Repositories;

use App\Helpers\SysHelper;
use Illuminate\Support\Arr;
use Mint\Service\Repositories\InitRepository;
use Illuminate\Validation\ValidationException;

class UpdateRepository
{
    protected $init;
    public function __construct(
        InitRepository $init
    ) {
        $this->init = $init;
    }
    public function download()
    {
        $info = $this->init->info();
        $product     = Arr::get($info, 'product', []);
        $build       = Arr::get($product, 'next_release_build');
        $version     = Arr::get($product, 'next_release_version');
        $update_size = Arr::get($product, 'next_release_size');
        if (! $version) {
            throw ValidationException::withMessages(['message' => trans('setup.update.not_available')]);
        }
        if (! $update_size) {
            throw ValidationException::withMessages(['message' => trans('setup.update.file_missing')]);
        }
        $ac = SysHelper::getApp('ACCESS_CODE');
        $e = SysHelper::getApp('EMAIL');
        $c = SysHelper::getApp('INSTALLED');
        $v = SysHelper::getApp('VERSION');
        $l = \Auth::check() ? \Auth::user()->email : null;
        $url = config('app.verifier').'/api/cc?a=download&u='.url()->current().'&ac='.$ac.'&i='.config('app.item').'&e='.$e.'&c='.$c.'&v='.$v.'&l='.$l;
        $zipFile = '../'.$build.".zip";
        $zipResource = fopen($zipFile, "w");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FILE, $zipResource);
        $response = curl_exec($ch);
        curl_close($ch);
        return array('build' => $build, 'version' => $version);
    }
    
    public function update()
    {
        $info = $this->init->info();
        $product     = Arr::get($info, 'product', []);
        $build       = Arr::get($product, 'next_release_build');
        $version     = Arr::get($product, 'next_release_version');
        $update_size = Arr::get($product, 'next_release_size');
        $input_build = request('build');
        $input_version = request('version');
        if (! $version || $build != $input_build || $version != $input_version) {
            throw ValidationException::withMessages(['message' => trans('general.invalid_action')]);
        }
        $zip = new \ZipArchive;
        if (! $zip) {
            throw ValidationException::withMessages(['message' => trans('setup.update.missing_zip_extension')]);
        }
        if (! \File::exists('../'.$build.".zip")) {
            throw ValidationException::withMessages(['message' => trans('setup.update.missing_file')]);
        }
        if ($zip->open('../'.$build.".zip") === true) {
            $zip->extractTo(base_path());
            $zip->close();
        } else {
            unlink('../'.$build.".zip");
            throw ValidationException::withMessages(['message' => trans('setup.update.zip_file_corrupted')]);
        }
        \Artisan::call('view:clear');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');
        \Artisan::call('migrate', ['--force' => true]);
        SysHelper::setApp([
            'VERSION' => $version
        ]);
        unlink('../'.$build.".zip");
    }
}
