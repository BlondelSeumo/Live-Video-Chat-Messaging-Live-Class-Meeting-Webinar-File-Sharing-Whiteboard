<?php
namespace Mint\Service\Controllers;

use App\Http\Controllers\Controller;
use Mint\Service\Repositories\InitRepository;
use Mint\Service\Repositories\UpdateRepository;

class UpdateController extends Controller
{
    protected $repo;
    protected $init;

    /**
     * Instantiate a new controller instance.
     */
    public function __construct(
        UpdateRepository $repo,
        InitRepository $init
    ) {
        $this->repo = $repo;
        $this->init = $init;
        $this->middleware('restricted_test_mode_action')->only(['download','update']);
    }

    /**
     * Download update
     */
    public function download()
    {
        $release = $this->repo->download();

        return $this->success(['release' => $release, 'message' => trans('setup.update.downloaded')]);
    }

    /**
     * Update product
     */
    public function update()
    {
        $this->repo->update();

        return $this->success(['message' => trans('setup.update.completed')]);
    }
}
