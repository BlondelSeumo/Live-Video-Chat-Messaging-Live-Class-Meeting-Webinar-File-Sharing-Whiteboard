<?php

namespace App\Http\Middleware;

use App\Helpers\SysHelper;
use Closure;

class IsSiteEnabled
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
        if (! config('config.website.enabled')) {
            return redirect('/app');
        }

        return $next($request);
    }
}
