<?php

namespace App\Policies\Auth;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view all the user
     * @param User $user
     */
    public function view(User $user) : bool
    {
        return $user->can('list-user');
    }

    /**
     * Determine whether the user can create users
     * @param User $user
     */
    public function create(User $user) : bool
    {
        return $user->can('create-user');
    }

    /**
     * Determine whether the user can view the user
     * @param User $user
     */
    public function show(User $user) : bool
    {
        return $user->can('list-user') || $user->id === \Auth::id();
    }

    /**
     * Determine whether the user can update the user
     * @param User $user
     */
    public function update(User $user) : bool
    {
        return $user->can('edit-user');
    }

    /**
     * Determine whether the user can delete the user
     * @param User $user
     */
    public function delete(User $user) : bool
    {
        return $user->can('delete-user');
    }
}