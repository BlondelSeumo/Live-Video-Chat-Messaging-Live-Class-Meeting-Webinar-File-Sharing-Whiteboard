<?php

namespace App\Listeners;

use App\Events\UserLogin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Mint\Service\Repositories\InitRepository;

class UserLoginListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected $init;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(InitRepository $init)
    {
        $this->init = $init;
    }

    /**
     * Handle the event.
     *
     * @param  UserLogin  $event
     * @return void
     */
    public function handle(UserLogin $event)
    {
        $this->init->check();
    }
}
