<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Repositories\Auth\LoginRepository;

class LoginController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        LoginRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * User login
     * @post ("/api/auth/login")
     * @param ({
     *      @Parameter("email", type="string", required="true", description="User email or username"),
     *      @Parameter("password", type="string", required="true", description="User password"),
     *      @Parameter("device_name", type="string", required="optional", description="User device type"),
     * })
     * @return array
     */
    public function login(LoginRequest $request)
    {
        return $this->success($this->repo->login());
    }

    /**
     * User logout
     * @post ("/api/auth/logout")
     * @return array
     */
    public function logout()
    {
        $this->repo->logout();

        return $this->ok(['message' => __('auth.login.logged_out')]);
    }
}
