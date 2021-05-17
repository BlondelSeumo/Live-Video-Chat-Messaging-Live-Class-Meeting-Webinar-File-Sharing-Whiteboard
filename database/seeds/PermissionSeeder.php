<?php

use App\Helpers\ArrHelper;
use App\Models\Config\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use App\Models\Config\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $acl = ArrHelper::getVar('acl');

        $existing_permissions = Permission::get()->pluck('name')->all();
        $system_permissions   = Arr::get($acl, 'permissions', []);

        $permissions = array();
        foreach ($system_permissions as $system_permission) {
            [$keys, $values]   = Arr::divide($system_permission);
            $permissions = array_merge($permissions, $keys);
        }
        
        $new_permissions = array_diff($permissions, $existing_permissions);

        $permissions = array();
        foreach ($new_permissions as $permission) {
            $permissions[] = array(
                'uuid' => (string) Str::uuid(),
                'name' => $permission,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            );
        }

        Permission::insert($permissions);
    }
}
