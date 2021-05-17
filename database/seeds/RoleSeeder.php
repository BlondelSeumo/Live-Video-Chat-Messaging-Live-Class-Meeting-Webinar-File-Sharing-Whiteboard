<?php

use App\Helpers\ArrHelper;
use App\Models\Config\Role;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $acl = ArrHelper::getVar('acl');

        $existing_roles = Role::get()->pluck('name')->all();
        $system_roles   = Arr::get($acl, 'roles', []);
        $new_roles      = array_diff($system_roles, $existing_roles);

        $roles = array();
        foreach ($new_roles as $role) {
            $roles[] = array(
                'uuid' => (string) Str::uuid(),
                'name' => $role,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            );
        }

        Role::insert($roles);
    }
}
