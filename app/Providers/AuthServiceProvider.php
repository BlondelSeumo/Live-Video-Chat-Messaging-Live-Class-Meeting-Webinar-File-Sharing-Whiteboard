<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\User'            => 'App\Policies\Auth\UserPolicy',
        'App\Models\Utility\Todo'    => 'App\Policies\Utility\TodoPolicy',
        'App\Models\Site\Query'      => 'App\Policies\Site\QueryPolicy',
        'App\Models\Site\Subscriber' => 'App\Policies\Site\SubscriberPolicy',
        'App\Models\Meeting'         => 'App\Policies\MeetingPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
