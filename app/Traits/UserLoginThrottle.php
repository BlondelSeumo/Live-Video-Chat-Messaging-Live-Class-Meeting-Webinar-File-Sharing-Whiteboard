<?php

namespace App\Traits;

use App\Helpers\CalHelper;
use App\Helpers\IpHelper;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

trait UserLoginThrottle
{
    /**
     * Validate login throttle
     */
    public function throttleValidate() : void
    {
        $ip = IpHelper::getRemoteIPAddress();

        if (config('config.auth.login_throttle') &&
            \Cache::has($ip) &&
            \Cache::has('last_login_attempt') &&
            \Cache::get($ip) >= config('config.auth.login_throttle_attempt')
        ) {
            $last_login_attempt = \Cache::get('last_login_attempt');

            $throttle_timeout = Carbon::parse($last_login_attempt)->addMinutes(config('config.auth.login_throttle_timeout'))->toDateTimeString();

            if ($throttle_timeout >= Carbon::now()->toDateTimeString()) {
                throw ValidationException::withMessages(['email' => __('auth.login_throttle_limit_crossed', ['attribute' => CalHelper::showTime($throttle_timeout)])]);
            } else {
                \Cache::forget($ip);
                \Cache::forget('last_login_attempt');
            }
        }
    }

    /**
     * Update login throttle cache.
     */
    public function throttleUpdate() : void
    {
        if (! config('config.auth.login_throttle')) {
            return;
        }

        $ip = IpHelper::getRemoteIPAddress();

        if (\Cache::has($ip)) {
            $throttle_attempt = \Cache::get($ip) + 1;
        } else {
            $throttle_attempt = 1;
        }

        cache([$ip => $throttle_attempt], 300);
        cache(['last_login_attempt' => Carbon::now()->toDateTimeString()], 300);
    }

    /**
     * Clear login throttle cache.
     */
    public function throttleClear() : void
    {
        $ip = IpHelper::getRemoteIPAddress();

        \Cache::forget($ip);
        \Cache::forget('last_login_attempt');
    }
}
