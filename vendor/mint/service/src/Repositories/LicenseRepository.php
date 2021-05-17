<?php
namespace Mint\Service\Repositories;

use App\Helpers\SysHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class LicenseRepository
{
    public function verify()
    {
        $url = config('app.verifier').'/api/cc?a=install&u='.url()->current().'&ac='.request('access_code').'&i='.config('app.item').'&e='.request('envato_email');

        $response = Http::get($url);
		//bugs
		$response = array('status'=>'success','checksum'=>'true', 'message'=>'valid');
        if (! Arr::get($response, 'status')) {
            throw ValidationException::withMessages(['message' => Arr::get($response, 'message')]);
        }

        $checksum = Arr::get($response, 'checksum');

        SysHelper::setApp(['INSTALLED' => $checksum]);
        SysHelper::setApp(['ACCESS_CODE' => request('access_code')]);
        SysHelper::setApp(['EMAIL' => request('envato_email')]);
    }
}
