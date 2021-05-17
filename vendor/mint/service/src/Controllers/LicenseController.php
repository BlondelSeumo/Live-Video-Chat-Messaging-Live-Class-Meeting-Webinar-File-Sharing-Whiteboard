<?php
namespace Mint\Service\Controllers;

use App\Http\Controllers\Controller;
use Mint\Service\Requests\LicenseRequest;
use Mint\Service\Repositories\LicenseRepository;

class LicenseController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance.
     */
    public function __construct(
        LicenseRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Verify license
     */
    public function verify(LicenseRequest $request)
    {
        $this->repo->verify();

        return $this->success(['message' => trans('setup.license.verified')]);
    }
}
