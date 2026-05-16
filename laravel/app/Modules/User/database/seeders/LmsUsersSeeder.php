<?php

namespace User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use User\Enums\EducationForm;
use User\Enums\UserRole;
use User\Models\StudentGroup;
use User\Models\StudentProfile;
use User\Models\User;

class LmsUsersSeeder extends Seeder
{
    public function run(): void
    {
        $fullTimeGroup = StudentGroup::query()->firstOrCreate(
            ['name' => 'ИВТ-401', 'education_form' => EducationForm::FullTime, 'course' => 4],
        );

        $partTimeGroup = StudentGroup::query()->firstOrCreate(
            ['name' => 'ЭКЗ-201', 'education_form' => EducationForm::PartTime, 'course' => 2],
        );

        User::query()->firstOrCreate(
            ['email' => 'admin@institute.local'],
            [
                'role' => UserRole::Administrator,
                'last_name' => 'Системов',
                'first_name' => 'Админ',
                'second_name' => 'Админович',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        $teacher = User::query()->firstOrCreate(
            ['email' => 'teacher@institute.local'],
            [
                'role' => UserRole::Teacher,
                'last_name' => 'Петров',
                'first_name' => 'Иван',
                'second_name' => 'Сергеевич',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        $student = User::query()->firstOrCreate(
            ['email' => 'student@institute.local'],
            [
                'role' => UserRole::Student,
                'last_name' => 'Иванов',
                'first_name' => 'Алексей',
                'second_name' => 'Дмитриевич',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        StudentProfile::query()->firstOrCreate(
            ['user_id' => $student->id],
            [
                'group_id' => $fullTimeGroup->id,
                'student_id_number' => 'ST-2024-0001',
            ],
        );

        $partTimeStudent = User::query()->firstOrCreate(
            ['email' => 'student-parttime@institute.local'],
            [
                'role' => UserRole::Student,
                'last_name' => 'Сидорова',
                'first_name' => 'Мария',
                'second_name' => 'Андреевна',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        StudentProfile::query()->firstOrCreate(
            ['user_id' => $partTimeStudent->id],
            [
                'group_id' => $partTimeGroup->id,
                'student_id_number' => 'ST-2024-0102',
            ],
        );

        $this->command?->info('LMS users seeded. Teacher id: '.$teacher->id);
    }
}
