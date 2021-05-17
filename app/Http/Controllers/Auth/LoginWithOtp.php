<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginWithOtpRequest;
use App\Repositories\Auth\LoginWithOtpRepository;

class LoginWithOtp extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        LoginWithOtpRepository $repo
    ) {
        $this->repo = $repo;
        
        $this->middleware('otp_login:email');
    }

    /**
     * Login with otp
     * @post ("/api/auth/login/otp")
     * @param ({
     *      @Parameter("email", type="string", required="optional", description="User email"),
     *      @Parameter("mobile", type="string", required="optional", description="User mobile"),
     *      @Parameter("otp", type="string", required="required", description="One time password"),
     * })
     * @return array
     */
    public function __invoke(LoginWithOtpRequest $request)
    {
        $response = $this->repo->login();

        if (is_bool($response)) {
            return $this->ok([]);
        }

        return $this->success($response);
    }
}