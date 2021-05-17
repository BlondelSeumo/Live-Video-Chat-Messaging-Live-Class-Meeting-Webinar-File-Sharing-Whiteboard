<?php

namespace App\Policies\Utility;

use App\Models\User;
use App\Models\Utility\Todo;
use Illuminate\Auth\Access\HandlesAuthorization;

class TodoPolicy
{
    use HandlesAuthorization;

    public function before($user, $ability) : bool
    {
        return $user->can('access-todo');
    }

    /**
     * Determine whether the user can view all the todo
     * @param User $user
     * @param Todo $todo
     */
    public function view(User $user) : bool
    {
        return true;
    }

    /**
     * Determine whether the user can create todos
     * @param User $user
     */
    public function create(User $user) : bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the todo
     * @param User $user
     * @param Todo $todo
     */
    public function show(User $user, Todo $todo) : bool
    {
        return $todo->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the todo
     * @param User $user
     * @param Todo $todo
     */
    public function update(User $user, Todo $todo) : bool
    {
        return $todo->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the todo
     * @param User $user
     * @param Todo $todo
     */
    public function delete(User $user, Todo $todo) : bool
    {
        return $todo->user_id === $user->id;
    }
}
