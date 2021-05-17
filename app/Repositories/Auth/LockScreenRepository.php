<?php
namespace App\Repositories\Auth;

use Illuminate\Validation\ValidationException;

class LockScreenRepository
{
    /**
     * Validate lock screen password
     */
    public function lockScreen() : void
    {
        if (! \Hash::check(request('password'), \Auth::user()->password)) {
            throw ValidationException::withMessages(['password' => __('auth.login.failed')]);
        }
    }
}
