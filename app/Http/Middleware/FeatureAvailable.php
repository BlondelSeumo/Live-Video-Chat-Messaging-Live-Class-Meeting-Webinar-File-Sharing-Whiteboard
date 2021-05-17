<?php

namespace App\Http\Middleware;

use Closure;

class FeatureAvailable
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
        if (! config('config.system.'.$feature)) {
            return response()->json(['message' => __('general.feature_not_available')], 399);
        }
        
        return $next($request);
    }
}
