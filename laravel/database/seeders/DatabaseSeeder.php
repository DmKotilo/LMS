<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use User\Database\Seeders\LmsUsersSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MoonShineAdminSeeder::class,
            LmsUsersSeeder::class,
        ]);
    }
}
