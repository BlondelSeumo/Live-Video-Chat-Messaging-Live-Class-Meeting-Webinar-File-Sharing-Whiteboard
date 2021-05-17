<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LockScreenRequest;
use App\Repositories\Auth\LockScreenRepository;

class LockScreen extends Controller
{
    protected $request;
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        LockScreenRepository $repo
    ) {
        $this->request = $request;
        $this->repo = $repo;
    }

    /**
     * Get lock screen
     * @post ("/api/auth/lock")
     * @param ({
     *      @Parameter("password", type="string", required="true", description="User password"),
     * })
     * @return array
     */
    public function __invoke(LockScreenRequest $request)
    {
        $this->repo->lockScreen();

        return $this->ok([]);
    }
}
