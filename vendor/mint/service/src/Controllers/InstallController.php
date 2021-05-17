<?php
namespace Mint\Service\Controllers;

use App\Http\Controllers\Controller;
use Mint\Service\Requests\InstallRequest;
use Mint\Service\Repositories\InstallRepository;

class InstallController extends Controller
{
    protected $repo;

    /**
     * Instantiate a new controller instance.
     */
    public function __construct(
        InstallRepository $repo
    ) {
        $this->repo = $repo;
    }

    /**
     * Force migrate
     */
    public function forceMigrate() {
        return $this->ok($this->repo->forceMigrate());
    }

    /**
     * Used to get pre requisites of server and folder
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Used to install the application
     */
    public function store(InstallRequest $request)
    {
        $this->repo->validateDatabase();

        if (in_array(request()->query('option'), ['database', 'admin', 'access_code'])) {
            return $this->success([]);
        }

        $this->repo->install();

        return $this->success(['message' => trans('setup.install.completed')]);
    }
}