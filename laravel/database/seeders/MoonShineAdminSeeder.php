<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;

class MoonShineAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('MOONSHINE_ADMIN_EMAIL', 'admin@mail.com');
        $password = env('MOONSHINE_ADMIN_PASSWORD', 'admin');
        $name = env('MOONSHINE_ADMIN_NAME', 'Admin');

        MoonshineUser::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'moonshine_user_role_id' => MoonshineUserRole::DEFAULT_ROLE_ID,
            ]
        );

        $this->command->info("MoonShine admin created: {$email}");
    }
}

