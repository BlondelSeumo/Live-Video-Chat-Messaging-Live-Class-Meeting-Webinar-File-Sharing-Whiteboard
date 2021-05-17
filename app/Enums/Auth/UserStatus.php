<?php

namespace App\Enums\Auth;

class UserStatus
{
    const ACTIVATED = 'activated';
    const PENDING_APPROVAL = 'pending_approval';
    const DISAPPROVED = 'disapproved';
    const PENDING_ACTIVATION = 'pending_activation';
    const BANNED = 'banned';
}
