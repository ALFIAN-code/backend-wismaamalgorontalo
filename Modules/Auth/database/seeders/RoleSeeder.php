<?php

namespace Modules\Auth\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'super-admin', 'guard_name' => 'api']);
        Role::updateOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        Role::updateOrCreate(['name' => 'resident', 'guard_name' => 'api']);
        Role::updateOrCreate(['name' => 'member', 'guard_name' => 'api']);
    }
}
