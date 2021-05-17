<?php

namespace App\Policies\Site;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QueryPolicy
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
     * Determine whether the user can list all the query.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Query  $query
     * @return boolean
     */
    public function list(User $user)
    {
        return $user->can('list-query');
    }

    /**
     * Determine whether the user can view the query.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Query  $query
     * @return boolean
     */
    public function show(User $user)
    {
        return $user->can('list-query');
    }

    /**
     * Determine whether the user can update the query.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Query  $query
     * @return boolean
     */
    public function update(User $user)
    {
        return $user->can('response-query');
    }

    /**
     * Determine whether the user can delete the query.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Query  $query
     * @return boolean
     */
    public function delete(User $user)
    {
        return $user->can('delete-query');
    }
}