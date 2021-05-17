<?php

namespace App\Http\Middleware;

use Closure;
use App\Repositories\Config\ConfigRepository;
use Mint\Service\Repositories\InitRepository;

class Init
{
    protected $config;
    protected $init;

    public function __construct(
        ConfigRepository $config,
        InitRepository $init
    ) {
        $this->config = $config;
        $this->init = $init;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->config->setDefault();

        return $next($request);
    }
}
