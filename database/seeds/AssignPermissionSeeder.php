<?php

use App\Helpers\ArrHelper;
use App\Models\Config\Permission;
use App\Models\Config\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AssignPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $acl = ArrHelper::getVar('acl');
        $system_permissions = Arr::get($acl, 'permissions', []);

        $roles = Role::all();
        $permissions = Permission::all();
        $admin_role = $roles->firstWhere('name', 'admin');

        $role_permission = array();
        foreach ($permissions as $permission) {
            $role_permission[] = array(
                'permission_id' => $permission->id,
                'role_id' => $admin_role->id,
            );
        }

        foreach ($system_permissions as $permission_group) {
            foreach ($permission_group as $name => $assigned_roles) {
                foreach ($assigned_roles as $role) {
                    $role_permission[] = array(
                        'permission_id' => $permissions->firstWhere('name', $name)->id,
                        'role_id' => $roles->firstWhere('name', $role)->id
                    );
                }
            }
        }

        \DB::table('role_has_permissions')->insert($role_permission);
    }
}
