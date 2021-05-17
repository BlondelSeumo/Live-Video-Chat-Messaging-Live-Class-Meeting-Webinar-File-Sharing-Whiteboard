<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Http\Resources\AuthUser;
use App\Traits\UserLoginThrottle;
use App\Traits\TwoFactorSecurity;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginRepository
{
    use UserLoginThrottle, TwoFactorSecurity;

    protected $user;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        User $user
    ) {
        $this->user = $user;
    }

    /**
     * Authenticate user
     */
    public function login() : array
    {
        $this->throttleValidate();

        $user = request('device_name') ? $this->validateDeviceLogin() : $this->validateLogin();

        $user->validateStatus();

        $this->throttleClear();

        $this->set($user);

        return [
            'message'        => __('auth.login.logged_in'),
            'user'           => new AuthUser($user),
            'token'          => request('device_name') ? $user->createToken(request('device_name'))->plainTextToken : null,
            'two_factor_set' => config('config.auth.two_factor_security') ? true : false,
        ];
    }

    /**
     * Validate login credentials
     */
    public function validateLogin() : User
    {
        if (filter_var(request('email'), FILTER_VALIDATE_EMAIL)) {
            $credentials = array('email' => request('email'), 'password' => request('password'));
        } else {
            $credentials = array('username' => request('email'), 'password' => request('password'));
        }

        if (! \Auth::attempt($credentials)) {
            $this->throttleUpdate();
            throw ValidationException::withMessages(['email' => __('auth.login.failed')]);
        }

        return \Auth::user();
    }

    /**
     * Validate device login credentials
     */
    public function validateDeviceLogin() : User
    {
        if (filter_var(request('email'), FILTER_VALIDATE_EMAIL)) {
            $user = User::whereEmail(request('email'))->first();
        } else {
            $user = User::whereUsername(request('email'))->first();
        }

        if (! $user || ! Hash::check(request('password'), $user->password)) {
            $this->throttleUpdate();
            throw ValidationException::withMessages(['email' => __('auth.login.failed')]);
        }

        return $user;
    }

    /**
     * Logout user
     */
    public function logout() : void
    {
        \Auth::user()->logout();
    }
}
