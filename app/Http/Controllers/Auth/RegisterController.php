<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Auth\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\Auth\RegisterRepository;

class RegisterController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        RegisterRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * User registration
     * @post ("/api/auth/register")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="User name"),
     *      @Parameter("email", type="email", required="true", description="User email"),
     *      @Parameter("username", type="string", required="true", description="User username"),
     *      @Parameter("password", type="string", required="true", description="User password"),
     *      @Parameter("confirm_password", type="string", required="optional", description="User confirm password"),
     * })
     * @return array
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->repo->register();

        return $this->success(['message' => __('auth.register.' . $user->status . '_message'), 'registration_status' => $user->status ]);
    }

    /**
     * User verification
     * @post ("/api/auth/verify")
     * @param ({
     *      @Parameter("uuid", type="string", required="true", description="User activation token"),
     * })
     * @return array
     */
    public function verify()
    {
        $this->repo->verify();

        return $this->success(['message' => __('auth.register.user_verified')]);
    }
}