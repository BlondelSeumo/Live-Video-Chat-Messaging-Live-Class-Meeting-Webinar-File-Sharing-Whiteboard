<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\ArrHelper;

class XssProtection
{
    /**
     * Prevent Cross Site-Scripting
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->is('livewire/*')) {
            return $next($request);
        }

        $except = array();
        foreach (ArrHelper::getVar('xss') as $key => $value) {
            if ($request->is($key)) {
                $except = $value;
            }
        }

        $input = $request->except($except);

        array_walk_recursive($input, function (&$input) {
            $input = strip_tags($input);
        });

        $request->merge($input);

        return $next($request);
    }
}
