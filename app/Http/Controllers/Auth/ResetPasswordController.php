<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Repositories\Auth\ResetPasswordRepository;
use App\Http\Requests\Auth\ResetPasswordEmailRequest;

class ResetPasswordController extends Controller
{
    protected $request;
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        ResetPasswordRepository $repo
    ) {
        $this->request = $request;
        $this->repo = $repo;
    }

    /**
     * Request reset password
     * @post ("/api/auth/password")
     * @param ({
     *      @Parameter("email", type="email", required="true", description="User email"),
     * })
     * @return array
     */
    public function password(ResetPasswordEmailRequest $request)
    {
        $this->repo->password();

        return $this->success(['message' => __('auth.password.sent')]);
    }

    /**
     * Validate user password
     * @post ("/api/auth/validate-reset-password")
     * @param ({
     *      @Parameter("email", type="email", required="true", description="User email"),
     *      @Parameter("code", type="string", required="true", description="Reset password code"),
     * })
     * @return array
     */
    public function validateCode(ResetPasswordEmailRequest $request)
    {
        $this->repo->validate();

        return $this->ok([]);
    }

    /**
     * Reset user password
     * @post ("/api/auth/reset")
     * @param ({
     *      @Parameter("code", type="string", required="true", description="Reset password code"),
     *      @Parameter("email", type="email", required="true", description="User email"),
     *      @Parameter("password", type="password", required="true", description="User new password"),
     *      @Parameter("password_confirmation", type="password", required="true", description="User new confirm password"),
     * })
     * @return array
     */
    public function reset(ResetPasswordRequest $request)
    {
        $this->repo->reset();

        return $this->success(['message' => __('global.reset', ['attribute' => __('auth.login.props.password')])]);
    }
}
