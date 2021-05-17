<?php

namespace App\Http\Controllers\Config;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Repositories\Config\PermissionRepository;
use App\Http\Resources\Config\Permission as PermissionResource;

class PermissionController extends Controller
{
    protected $request;
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        PermissionRepository $repo
    ) {
        $this->repo = $repo;
        $this->request = $request;

        $this->middleware('role:admin');
    }

    /**
     * Get pre requisite to assign permission
     * @get ("/api/config/permissions/pre-requisite")
     * @return array
     */
    public function preRequisite()
    {
        return $this->ok($this->repo->getPreRequisite());
    }

    /**
     * Assign permission to different roles
     * @post ("/api/config/permissions/assign")
     * @param ({
     *      @Parameter("data", type="array", required="true", description="Permission array"),
     * })
     * @return Response
     */
    public function assign()
    {
        $this->repo->assign();

        activity('permission')->log('assigned');

        return $this->success(['message' => __('global.assigned', ['attribute' => __('config.permission.permission')])]);
    }
}
