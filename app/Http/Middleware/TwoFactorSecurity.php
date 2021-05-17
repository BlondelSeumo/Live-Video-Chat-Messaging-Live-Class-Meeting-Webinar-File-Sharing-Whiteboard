<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class TwoFactorSecurity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('config.auth.two_factor_security') && session()->exists('2fa')) {
            throw new AuthenticationException(__('auth.security.two_factor_security_pending'));
        }

        return $next($request);
    }
}
