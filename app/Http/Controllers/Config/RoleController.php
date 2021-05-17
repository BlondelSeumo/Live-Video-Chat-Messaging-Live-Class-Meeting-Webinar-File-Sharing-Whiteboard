<?php

namespace App\Http\Controllers\Config;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Config\RoleRequest;
use App\Repositories\Config\RoleRepository;
use App\Http\Resources\Config\Role as RoleResource;

class RoleController extends Controller
{
    protected $request;
    protected $repo;

    /**
     * Instantiate a new controller instance
     * @return void
     */
    public function __construct(
        Request $request,
        RoleRepository $repo
    ) {
        $this->request = $request;
        $this->repo = $repo;

        $this->middleware('role:admin', ['except' => ['list']]);
    }

    /**
     * Get all roles
     * @get ("/api/config/roles")
     * @return array
     */
    public function index()
    {
        return $this->repo->paginate();
    }

    /**
     * Store a role
     * @post ("/api/config/roles")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Role name"),
     * })
     * @return array
     */
    public function store(RoleRequest $request)
    {
        $role = Role::create(['name' => strtolower(request('name')), 'uuid' => Str::uuid(), 'guard_name' => 'web']);

        activity('role')->on($role)->withProperties([
            'attributes' => [
                'id'   => $role->id,
                'name' => $role->name
            ]
        ])->log('created');

        return $this->success(['message' => __('global.added', ['attribute' => __('config.role.role')])]);
    }

    /**
     * Get role detail
     * @post ("/api/config/roles/{name}")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Role name"),
     * })
     * @return resource
     */
    public function show($name)
    {
        return new RoleResource($this->repo->findByNameOrFail($name));
    }

    /**
     * Delete role
     * @delete ("/api/config/roles")
     * @param ({
     *      @Parameter("name", type="string", required="true", description="Role name"),
     * })
     * @return array
     */
    public function destroy($name)
    {
        $this->repo->delete($name);

        return $this->success(['message' => __('global.deleted', ['attribute' => __('config.role.role')])]);
    }
}
