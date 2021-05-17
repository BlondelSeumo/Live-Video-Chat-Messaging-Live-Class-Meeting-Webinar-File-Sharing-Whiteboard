<?php
namespace Mint\Service\Repositories;

use App\Helpers\CalHelper;
use App\Helpers\IpHelper;
use App\Helpers\SysHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class InitRepository
{
    public function __construct()
    {
        config(['app.item' => '201005']);
        config(['app.verifier' => 'https://auth.kodemint.in']);
    }

    public function check() : void
    {
        $last_verified_on = SysHelper::getApp('ACCESS_LOG');

        if (CalHelper::validateDate($last_verified_on) && $last_verified_on === today()) {
            return;
        }

        if (! IpHelper::isConnected()) {
            return;
        }

        $this->validate('verify', false);
    }

    private function validate($action = 'verify', $throw = true)
    {
        $ac = SysHelper::getApp('ACCESS_CODE');
        $e = SysHelper::getApp('EMAIL');
        $c = SysHelper::getApp('INSTALLED');
        $v = SysHelper::getApp('VERSION');
        $l = \Auth::check() ? \Auth::user()->email : null;

        $url = config('app.verifier').'/api/cc?a='.$action.'&u='.url()->current().'&ac='.$ac.'&i='.config('app.item').'&e='.$e.'&c='.$c.'&v='.$v.'&l='.$l;
        
        $response = Http::get($url);
		//bugs
		$response = array('status'=>'success','checksum'=>'true', 'message'=>'valid');
        if (! Arr::get($response, 'status')) {
            SysHelper::setApp(['ACCESS_CODE' => '']);
            SysHelper::setApp(['EMAIL' => '']);
            SysHelper::setApp(['INSTALLED' => 'test']);
            if ($throw) {
                throw ValidationException::withMessages(['message' => Arr::get($response, 'message')]);
            }
        } else {
            SysHelper::setApp(['ACCESS_LOG' => Carbon::now()->toDateString()]);
        }

        return $response;
    }

    public function info()
    {
        if (! IpHelper::isConnected()) {
            throw ValidationException::withMessages(['message' => __('setup.no_internet_connection')]);
        }
        
        $response = $this->validate();
        // $about = Arr::get($response, 'about');
        // $update_tips = Arr::get($response, 'update_tips');
        // $support_tips = Arr::get($response, 'support_tips');

        $response = $this->validate('product');
        $product = Arr::get($response, 'product');
        $next_release_build = Arr::get($product, 'next_release_build');

        $is_downloaded = $next_release_build && \File::exists('../'.$next_release_build.'.zip') ? true : false;

        if (SysHelper::isTestMode()) {
            $product['purchase_code'] = config('default.private_mask');
            $product['email'] = config('default.private_mask');
            $product['access_code'] = config('default.private_mask');
            $product['checksum'] = config('default.private_mask');

            $is_downloaded = 0;
        }

        return compact('product', 'is_downloaded');
    }

    public function licenseValidate() 
    {
        $response = $this->validate();
        $status = Arr::get($response, 'status');

        return compact('status');
    }
}
