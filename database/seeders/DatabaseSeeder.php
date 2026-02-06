<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\database\seeders\AuthDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthDatabaseSeeder::class,
        ]);
    }
}
