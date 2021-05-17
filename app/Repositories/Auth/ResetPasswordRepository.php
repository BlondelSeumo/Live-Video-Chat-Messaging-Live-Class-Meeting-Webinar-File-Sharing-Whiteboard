<?php
namespace App\Repositories\Auth;

use Carbon\Carbon;
use App\Models\User;
use App\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use App\Notifications\ResetPasswordEmail;
use Illuminate\Validation\ValidationException;

class ResetPasswordRepository
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
     * Validate reset password availability
     */
    private function isResetPasswordEnabled() : void
    {
        if (! config('config.auth.reset_password')) {
            throw ValidationException::withMessages(['message' => __('general.feature_not_available')]);
        }
    }

    /**
     * Request password reset
     */
    public function password() : void
    {
        $this->isResetPasswordEnabled();

        $user = $this->getUser();

        $code = rand(100000, 999999);

        \DB::table('password_resets')->insert([
            'email'      => request('email'),
            'code'       => $code,
            'created_at' => now()
        ]);

        $user->notify(new ResetPasswordEmail($user, $code));
    }

    /**
     * Validate user for reset password
     */
    private function getUser() : User
    {
        $user = $this->user->filterByEmail(request('email'), 1)->first();

        if (! $user) {
            throw ValidationException::withMessages(['email' => __('auth.password.user')]);
        }

        if ($user->status != 'activated') {
            throw ValidationException::withMessages(['email' => __('auth.status.not_activated')]);
        }

        return $user;
    }

    /**
     * Validate reset password code
     */
    public function validate() : void
    {
        $reset = \DB::table('password_resets')->where('email', '=', request('email'))->where('code', '=', request('code'))->first();

        if (! $reset) {
            throw ValidationException::withMessages(['message' => __('auth.password.token')]);
        }

        if (Carbon::now()->addMinutes(config('config.auth.reset_password_token_lifetime')) < Carbon::now()) {
            throw ValidationException::withMessages(['email' => __('auth.password.token_expired')]);
        }
    }

    /**
     * Reset password of user
     */
    public function reset() : void
    {
        $email    = request('email');
        $code     = request('code');

        $this->isResetPasswordEnabled();
    
        $user = $this->getUser();

        $this->validate();

        if (Hash::check(request('new_password'), $user->password)) {
            throw ValidationException::withMessages(['message' => __('auth.password.different')]);
        }

        $user->password = bcrypt(request('new_password'));
        $user->save();

        \DB::table('password_resets')->where('email', '=', $email)->where('code', '=', $code)->delete();

        $user->notify(new ResetPassword($user));
    }
}
