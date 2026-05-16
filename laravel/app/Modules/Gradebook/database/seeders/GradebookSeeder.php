<?php

namespace Gradebook\Database\Seeders;

use Gradebook\Models\Gradebook;
use Gradebook\Models\GradebookRow;
use Illuminate\Database\Seeder;
use User\Models\User;

class GradebookSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::query()->where('email', 'teacher@institute.local')->first();
        $student = User::query()->where('email', 'student@institute.local')->first();

        if (! $teacher || ! $student) {
            $this->command?->warn('Run LmsUsersSeeder first.');

            return;
        }

        $gradebook = Gradebook::query()->firstOrCreate(
            [
                'title' => 'Математический анализ — ИВТ-401',
                'teacher_id' => $teacher->id,
            ],
            [
                'discipline' => 'Математический анализ',
                'group_name' => 'ИВТ-401',
                'semester' => '1',
                'original_filename' => 'demo-gradebook.xlsx',
            ],
        );

        GradebookRow::query()->firstOrCreate(
            [
                'gradebook_id' => $gradebook->id,
                'student_id' => $student->id,
            ],
            [
                'student_name' => $student->fullName(),
                'group_name' => 'ИВТ-401',
                'semester' => '1',
                'module1_score' => 42,
                'module2_score' => 45,
                'total_score' => 87,
                'final_grade' => 'Отлично',
            ],
        );

        $gradebook2 = Gradebook::query()->firstOrCreate(
            [
                'title' => 'Программирование — ИВТ-401',
                'teacher_id' => $teacher->id,
                'semester' => '2',
            ],
            [
                'discipline' => 'Программирование',
                'group_name' => 'ИВТ-401',
            ],
        );

        GradebookRow::query()->firstOrCreate(
            [
                'gradebook_id' => $gradebook2->id,
                'student_id' => $student->id,
            ],
            [
                'student_name' => $student->fullName(),
                'group_name' => 'ИВТ-401',
                'semester' => '2',
                'module1_score' => 38,
                'module2_score' => 40,
                'total_score' => 78,
                'final_grade' => 'Хорошо',
            ],
        );

        $this->command?->info('Demo gradebooks seeded.');
    }
}
