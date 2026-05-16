<?php

namespace User\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use User\Enums\RoleEnum;
use User\Models\User;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        $password = '11111111';

        $userPhone = User::firstOrCreate(
            ['phone' => '9770000000'],
            [
                'name' => 'Иван',
                'last_name' => 'Петров',
                'second_name' => '(reg: Телефон)',
                'phone_verified_at' => Carbon::now(),
                'password' => Hash::make($password),
            ]
        );

        $userPhone->contactMethods()->sync([1, 2]);

        $userEmail = User::firstOrCreate(
            ['email' => 'test_user@gmail.com'],
            [
                'name' => 'Дмитрий',
                'last_name' => 'Лыськов',
                'second_name' => '(reg: Email)',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make($password),
            ]
        );

        $userEmail->contactMethods()->sync([2, 3]);
    }

}
