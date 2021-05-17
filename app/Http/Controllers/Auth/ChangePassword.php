<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Repositories\Auth\ChangePasswordRepository;

class ChangePassword extends Controller
{
    protected $request;
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        ChangePasswordRepository $repo
    ) {
        $this->request = $request;
        $this->repo = $repo;

        $this->middleware('restricted_test_mode_action');
    }

    /**
     * Change user password
     * @post ("/api/auth/change-password")
     * @param ({
     *      @Parameter("current_password", type="string", required="true", description="User current password"),
     *      @Parameter("new_password", type="string", required="true", description="User new password"),
     *      @Parameter("new_password_confirmation", type="string", required="true", description="User new confirm password"),
     * })
     * @return array
     */
    public function __invoke(ChangePasswordRequest $request)
    {
        $this->repo->changePassword();

        return $this->success(['message' => __('global.changed', ['attribute' => __('auth.login.props.password')])]);
    }
}
