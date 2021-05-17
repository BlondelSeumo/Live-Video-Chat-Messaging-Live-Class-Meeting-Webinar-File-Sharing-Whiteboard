<?php
namespace App\Repositories\Auth;

use Illuminate\Validation\ValidationException;

class ChangePasswordRepository
{
    /**
     * Change user password
     */
    public function changePassword() : void
    {
        if (! \Hash::check(request('current_password'), \Auth::user()->password)) {
            throw ValidationException::withMessages(['password' => __('auth.login.failed')]);
        }

        $user = \Auth::user();
        $user->password = bcrypt(request('new_password'));
        $user->save();
    }
}
