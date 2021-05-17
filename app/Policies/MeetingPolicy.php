<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeetingPolicy
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
     * Determine whether the user can fetch meeting pre requisite
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return boolean
     */
    public function preRequisite(User $user)
    {
        return $user->hasAnyPermission([
            'list-meeting',
            'create-meeting',
            'edit-meeting'
        ]);
    }

    /**
     * Determine whether the user can list all the meeting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return boolean
     */
    public function list(User $user)
    {
        return $user->can('list-meeting');
    }

    /**
     * Determine whether the user can create meeting.
     *
     * @param  \App\Models\User  $user
     * @return boolean
     */
    public function create(User $user)
    {
        return $user->can('create-meeting');
    }

    /**
     * Determine whether the user can view the meeting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return boolean
     */
    public function show(User $user)
    {
        return $user->can('list-meeting');
    }

    /**
     * Determine whether the user can update the meeting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return boolean
     */
    public function update(User $user)
    {
        return $user->can('edit-meeting');
    }

    /**
     * Determine whether the user can delete the meeting.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Meeting  $meeting
     * @return boolean
     */
    public function delete(User $user)
    {
        return $user->can('delete-meeting');
    }
}
