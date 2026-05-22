<?php

namespace User\Database\Seeders;

use Gradebook\Services\GradebookImportService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use User\Enums\EducationForm;
use User\Enums\UserRole;
use User\Models\StudentGroup;
use User\Models\StudentProfile;
use User\Models\User;

class LmsUsersSeeder extends Seeder
{
    private const GRADEBOOK_FILE = 'Докучаев_ДМО2401_ТВиМС_3 сем_2025-26-1.xlsx';

    private const GROUP_NAME = 'ДМО-2401';

    private const GROUP_COURSE = 2;

    public function run(): void
    {
        $gradebookPath = base_path(self::GRADEBOOK_FILE);
        if (! is_file($gradebookPath)) {
            $this->command?->error('Файл ведомости не найден: '.self::GRADEBOOK_FILE);

            return;
        }

        $parsed = app(GradebookImportService::class)->parseFromPath($gradebookPath);

        $group = StudentGroup::query()->firstOrCreate(
            [
                'name' => self::GROUP_NAME,
                'education_form' => EducationForm::FullTime,
                'course' => self::GROUP_COURSE,
            ],
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
            ['email' => 'dokuchaev@institute.local'],
            [
                'role' => UserRole::Teacher,
                'last_name' => 'Докучаев',
                'first_name' => 'Сергей',
                'second_name' => 'Анатольевич',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        $created = 0;
        foreach ($parsed['rows'] as $index => $row) {
            $name = $this->parseFullName($row['student_name']);
            if ($name['last_name'] === '') {
                continue;
            }

            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $email = "dmo2401-{$number}@institute.local";

            $student = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'role' => UserRole::Student,
                    'last_name' => $name['last_name'],
                    'first_name' => $name['first_name'],
                    'second_name' => $name['second_name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ],
            );

            StudentProfile::query()->updateOrCreate(
                ['user_id' => $student->id],
                [
                    'group_id' => $group->id,
                    'student_id_number' => 'DMO2401-'.$number,
                ],
            );

            $created++;
        }

        $discipline = $parsed['discipline'] ?? 'ведомость';
        $this->command?->info(sprintf(
            'LMS users seeded: группа %s (курс %d, очная), преподаватель %s, студентов %d из «%s».',
            self::GROUP_NAME,
            self::GROUP_COURSE,
            $teacher->fullName(),
            $created,
            $discipline,
        ));
    }

    /**
     * @return array{last_name: string, first_name: string, second_name: ?string}
     */
    private function parseFullName(string $fullName): array
    {
        $parts = preg_split('/\s+/u', trim($fullName), 3);

        return [
            'last_name' => $parts[0] ?? '',
            'first_name' => $parts[1] ?? '',
            'second_name' => $parts[2] ?? null,
        ];
    }
}
