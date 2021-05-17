<?php

namespace App\Console\Commands;

use App\Models\Config\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class SyncPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:permission {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync role and permissions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $force = $this->option('force');

        if (\App::environment('production') && ! $force) {
            $this->error('Could not sync in production mode');
            exit;
        }

        activity()->disableLogging();

        \Artisan::call('cache:clear');

        \Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => $force ? true : false]);
        \Artisan::call('db:seed', ['--class' => 'PermissionSeeder', '--force' => $force ? true : false]);
        
        $permissions = Permission::all()->pluck('name')->all();
        Role::whereName('admin')->first()->syncPermissions($permissions);

        activity()->enableLogging();

        $this->info('Roles & Permissions synced.');
    }
}