<?php

namespace App\Repositories\Auth;

use App\Models\User;
use App\Traits\EmailOtp;
use App\Http\Resources\AuthUser;
use Illuminate\Validation\ValidationException;
use App\Repositories\Auth\LoginWithOtpForMeetingRepository;

class LoginWithOtpRepository
{
    use EmailOtp;

    protected $user;
    protected $repo;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        User $user,
        LoginWithOtpForMeetingRepository $repo
    ) {
        $this->user = $user;
        $this->repo = $repo;
    }

    /**
     * Login with otp
     *
     * @return bool|array
     */
    public function login()
    {
        if (request()->has('email') && ! request()->has('otp')) {
            return $this->generateEmailOtp();
        } else if (request()->has('email') && request()->has('otp')) {
            return $this->validateEmailOtp();
        }
    }

    /**
     * Generate email otp
     */
    private function generateEmailOtp() : bool
    {
        $user = $this->user->whereEmail(request('email'))->first();

        if ($user) {
            $user->validateStatus(false); 
            $this->sendOtp($user);
            return true;
        }

        if ($this->repo->generateEmailOtp()) {
            return true;
        }

        throw ValidationException::withMessages(['email' => __('auth.password.user')]);
    }

    /**
     * Validate email otp
     */
    private function validateEmailOtp() : array
    {
        $user = $this->user->whereEmail(request('email'))->first();

        if ($user) {
            $this->validateCache($user);

            $user->validateStatus();

            if (request('device_name')) {
                $token = $user->createToken(request('device_name'))->plainTextToken;
            } else {
                \Auth::login($user);
            }
            
            return [
                'message' => __('auth.login.logged_in'),
                'user'    => new AuthUser($user),
                'token'   => $token ?? null
            ];
        }

        $response = $this->repo->validateEmailOtp();

        if (is_array($response)) {
            return $response;
        }

        throw ValidationException::withMessages(['email' => __('auth.password.user')]);
    }
}