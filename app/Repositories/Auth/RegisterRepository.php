<?php

namespace App\Repositories\Auth;

use App\Enums\Auth\UserStatus;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\UserEmailVerification;
use App\Notifications\UserRegistered;
use Illuminate\Validation\ValidationException;

class RegisterRepository
{
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
     * Register user
     */
    public function register() : User
    {
        if (! config('config.auth.registration')) {
            throw ValidationException::withMessages(['message' => trans('general.feature_not_available')]);
        }

        \DB::beginTransaction();

        $user = $this->user->create([
            'name'     => request('name'),
            'email'    => request('email'),
            'username' => request('username'),
            'password' => bcrypt(request('password')),
        ]);

        if (config('config.auth.email_verification')) {
            $status = UserStatus::PENDING_ACTIVATION;
        } else if (config('config.auth.account_approval')) {
            $status = UserStatus::PENDING_APPROVAL;
        } else {
            $status = UserStatus::ACTIVATED;
        }

        $user->status = $status;
        $user->meta = array('activation_token' => Str::uuid());
        $user->save();

        $user->assignRole('user');

        \DB::commit();

        if (config('config.auth.email_verification')) {
            $user->notify(new UserEmailVerification($user));
        }

        return $user;
    }

    /**
     * Verify user
     */
    public function verify() : void
    {
        $user = $this->user->where('meta->activation_token', request('token'))->first();

        if (! $user) {
            throw ValidationException::withMessages(['message' => __('auth.register.invalid_activation_token')]);
        }

        if ($user->status != UserStatus::PENDING_ACTIVATION) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }

        $user->email_verified_at = now();
        $user->status = config('config.auth.account_approval') ? UserStatus::PENDING_APPROVAL : UserStatus::ACTIVATED;
        $user->save();

        $user->notify(new UserRegistered($user));
    }
}