<?php

namespace Mint\Service;

use Illuminate\Support\ServiceProvider;

class MintServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes/web.php';
        include __DIR__.'/routes/api.php';
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Mint\Service\Controllers\InstallController');
        $this->app->make('Mint\Service\Controllers\UpdateController');
    }
}