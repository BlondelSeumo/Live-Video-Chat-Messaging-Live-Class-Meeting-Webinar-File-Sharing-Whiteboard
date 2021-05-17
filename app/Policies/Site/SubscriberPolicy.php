<?php

namespace App\Policies\Site;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubscriberPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can list all the subscriber.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subscriber  $subscriber
     * @return boolean
     */
    public function list(User $user)
    {
        return $user->can('list-subscriber');
    }

    /**
     * Determine whether the user can view the subscriber.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subscriber  $subscriber
     * @return boolean
     */
    public function show(User $user)
    {
        return $user->can('list-subscriber');
    }

    /**
     * Determine whether the user can delete the subscriber.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Subscriber  $subscriber
     * @return boolean
     */
    public function delete(User $user)
    {
        return $user->can('delete-subscriber');
    }
}