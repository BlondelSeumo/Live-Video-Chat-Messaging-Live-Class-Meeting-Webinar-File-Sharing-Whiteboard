<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomException;
use Closure;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Validation\ValidationException;

class UnderMaintenance
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
        if (\Auth::check() && ! \Auth::user()->hasRole('admin') && config('config.system.maintenance_mode')) {
            throw new CustomException([
                'message' => config('config.system.maintenance_mode_message'), 
                'cmd' => 'logout'
            ], 406);
        }

        return $next($request);
    }
}
