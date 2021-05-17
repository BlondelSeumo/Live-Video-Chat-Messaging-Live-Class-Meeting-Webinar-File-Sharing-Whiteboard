<?php

use App\Repositories\Config\ConfigRepository;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    protected $config;

    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->config->setDefault();

        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(AssignPermissionSeeder::class);
        $this->call(SitePageTemplateSeeder::class);
        $this->call(SitePageSeeder::class);
    }
}
