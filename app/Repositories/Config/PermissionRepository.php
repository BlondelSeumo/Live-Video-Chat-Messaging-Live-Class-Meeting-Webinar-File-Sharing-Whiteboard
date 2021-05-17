<?php
namespace App\Repositories\Config;

use App\Helpers\ArrHelper;
use Illuminate\Support\Arr;
use App\Models\Config\Permission;
use Spatie\Permission\Models\Role;
use App\Repositories\Config\RoleRepository;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Config\PermissionCollection;

class PermissionRepository
{
    protected $permission;
    protected $role;

    /**
     * Instantiate a new instance
     * @return void
     */
    public function __construct(
        Permission $permission,
        RoleRepository $role
    ) {
        $this->permission = $permission;
        $this->role = $role;
    }

    /**
     * Get pre requisite to permission assign
     */
    public function getPreRequisite() : array
    {
        $roles = $this->role->getAll([
            'exclude_admin' => true
        ]);

        $acl = ArrHelper::getVar('acl');

        $modules = array();
        foreach ($acl['permissions'] as $index => $permission_group) {
            array_push($modules, $index);
        }

        sort($modules);

        $all_permissions = Arr::get($acl, 'permissions');
        $permissions     = Arr::get($all_permissions, request('module'), []);
        $permissions     = array_keys($permissions);

        $assigned_permissions = array();

        foreach ($roles as $role) {
            $spatie_role = Role::findByName($role->name, 'web');
            $assigned_permissions[] = array(
                'role' => $role->name,
                'permissions' => $spatie_role->permissions->whereIn('name', $permissions)->pluck('name')->all(),
            );
        }

        return compact('roles', 'permissions', 'assigned_permissions', 'modules');
    }

    /**
     * Assign permission
     */
    public function assign() : void
    {
        $module = request('module');
        $roles = request('roles');
        $acl = ArrHelper::getVar('acl');

        $all_permissions = Arr::get($acl, 'permissions');
        $permissions = Arr::get($all_permissions, $module, []);
        $permissions = array_keys($permissions);

        if (! $permissions) {
            throw ValidationException::withMessages(['message' => __('general.invalid_action')]);
        }

        $role_with_permissions = Role::with('permissions')->get();

        foreach ($roles as $role) {
            $user_role = $role_with_permissions->where('name', strtolower(Arr::get($role, 'name')))->first();

            $assigned_permissions = $user_role->permissions->whereIn('name', $permissions)->pluck('name')->all();

            if ($user_role->name != 'admin') {
                $user_role->revokePermissionTo($assigned_permissions);
                $user_role->givePermissionTo(Arr::get($role, 'permissions', []));
            }
        }
    }
}
