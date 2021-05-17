<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\SysHelper;

class RestrictedActionInTestMode
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
        if (SysHelper::isTestMode()) {
            return response()->json(['message' => __('general.restricted_test_mode_action')], 399);
        }

        return $next($request);
    }
}
