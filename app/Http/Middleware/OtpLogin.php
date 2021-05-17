<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class OtpLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $feature)
    {
        if (! config('config.auth.' . $feature . '_otp_login')) {
            return response()->json(['message' => __('general.feature_not_available')], 422);
        }

        return $next($request);
    }
}