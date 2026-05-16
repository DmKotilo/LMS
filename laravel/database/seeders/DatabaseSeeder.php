<?php

namespace Database\Seeders;

use Gradebook\Database\Seeders\GradebookSeeder;
use Illuminate\Database\Seeder;
use User\Database\Seeders\LmsUsersSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MoonShineAdminSeeder::class,
            LmsUsersSeeder::class,
            GradebookSeeder::class,
        ]);
    }
}
