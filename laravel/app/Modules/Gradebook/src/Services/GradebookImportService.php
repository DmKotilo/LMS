<?php

namespace Gradebook\Services;

use Gradebook\Models\Gradebook;
use Gradebook\Models\GradebookRow;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;
use User\Enums\EducationForm;
use User\Enums\UserRole;
use User\Models\StudentGroup;
use User\Models\StudentProfile;
use User\Models\User;

class GradebookImportService
{
    public function importFromUploadedFile(
        UploadedFile $file,
        User $actor,
        string $semester,
        string $academicYear,
        ?int $teacherId = null,
    ): Gradebook
    {
        $targetTeacher = $this->resolveTeacher($actor, $teacherId);

        $storedPath = $file->store('gradebooks', 'local');
        $absolutePath = storage_path('app/'.$storedPath);

        $spreadsheet = IOFactory::load($absolutePath);
        $sheet = $spreadsheet->getActiveSheet();

        $parsed = $this->parseWorksheet($sheet);

        return DB::transaction(function () use ($parsed, $targetTeacher, $storedPath, $file, $semester, $academicYear) {
            $gradebook = Gradebook::query()->create([
                'title' => $parsed['title'] ?: ($parsed['discipline'] ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)),
                'discipline' => $parsed['discipline'],
                'group_name' => $parsed['group_name'],
                'semester' => $semester,
                'teacher_id' => $targetTeacher->id,
                'original_filename' => $file->getClientOriginalName(),
                'storage_path' => $storedPath,
                'direction_code' => $parsed['direction_code'],
                'academic_year' => $academicYear,
            ]);

            foreach ($parsed['rows'] as $row) {
                $student = $this->resolveStudent($row['student_name'], $row['group_name']);

                GradebookRow::query()->create([
                    'gradebook_id' => $gradebook->id,
                    'student_id' => $student?->id,
                    'student_name' => $row['student_name'],
                    'group_name' => $row['group_name'],
                    'semester' => $semester,
                    'module1_score' => $row['module1_score'],
                    'module2_score' => $row['module2_score'],
                    'total_score' => $row['total_score'],
                    'exam_score' => $row['exam_score'],
                    'final_grade' => $row['final_grade'],
                    'raw_data' => $row['raw_data'],
                ]);
            }

            return $gradebook->fresh(['teacher'])->loadCount('rows');
        });
    }

    private function resolveTeacher(User $actor, ?int $teacherId): User
    {
        if ($actor->role === UserRole::Teacher) {
            return $actor;
        }

        if ($teacherId !== null) {
            /** @var User $teacher */
            $teacher = User::query()->findOrFail($teacherId);
            if ($teacher->role !== UserRole::Teacher) {
                throw new RuntimeException('Выбранный пользователь не является преподавателем.');
            }

            return $teacher;
        }

        throw new RuntimeException('Для администратора нужно передать teacher_id.');
    }

    /**
     * @return array{
     *   title:string,
     *   discipline:?string,
     *   direction_code:?string,
     *   group_name:?string,
     *   rows:list<array{
     *      student_name:string,
     *      group_name:?string,
     *      module1_score:float,
     *      module2_score:float,
     *      total_score:float,
     *      exam_score:float,
     *      final_grade:?string,
     *      raw_data:array<string,mixed>
     *   }>
     * }
     */
    private function parseWorksheet(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestDataRow();
        $highestCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $discipline = null;
        $directionCode = null;
        $groupName = null;
        $teacherName = null;

        $headerRow = null;
        $studentNameCol = null;
        $module1TheoryCol = null;
        $module1PracticeCol = null;
        $module2TheoryCol = null;
        $module2PracticeCol = null;
        $mrsTotalCol = null;
        $examCol = null;
        $finalTotalCol = null;
        $gradeCol = null;

        for ($row = 1; $row <= min($highestRow, 15); $row++) {
            $line = $this->rowAsLine($sheet, $row, $highestCol);
            if ($line === '') {
                continue;
            }

            if ($discipline === null && preg_match('/Дисциплина:\s*(.+)$/ui', $line, $m)) {
                $discipline = trim($m[1]);
            }

            if ($directionCode === null && preg_match('/Направление:\s*([0-9\.]+)/ui', $line, $m)) {
                $directionCode = trim($m[1]);
            }

            if ($groupName === null && preg_match('/Группа:\s*([A-Za-zА-Яа-я0-9\-]+)/u', $line, $m)) {
                $groupName = trim($m[1]);
            }

            if ($teacherName === null && preg_match('/Преподаватель:\s*([^\n\r]+)/u', $line, $m)) {
                $teacherName = trim($m[1]);
            }

            if (preg_match('/Фамилия,\s*Имя,\s*Отчество/ui', $line)) {
                $headerRow = $row;

                for ($col = 1; $col <= $highestCol; $col++) {
                    $cellText = $this->cellString($sheet, $row, $col);
                    $normalized = mb_strtolower(trim($cellText));
                    if ($normalized === '') {
                        continue;
                    }

                    if ($studentNameCol === null && str_contains($normalized, 'фамилия')) {
                        $studentNameCol = $col;
                    } elseif ($module1TheoryCol === null && str_contains($normalized, 'теория')) {
                        $module1TheoryCol = $col;
                    } elseif ($module1PracticeCol === null && str_contains($normalized, 'практика')) {
                        $module1PracticeCol = $col;
                    } elseif ($module2TheoryCol === null && str_contains($normalized, 'теория')) {
                        $module2TheoryCol = $col;
                    } elseif ($module2PracticeCol === null && str_contains($normalized, 'практика')) {
                        $module2PracticeCol = $col;
                    } elseif ($mrsTotalCol === null && str_contains($normalized, 'итог по мрс')) {
                        $mrsTotalCol = $col;
                    } elseif ($examCol === null && str_contains($normalized, 'баллы за')) {
                        $examCol = $col;
                    } elseif ($finalTotalCol === null && str_contains($normalized, 'итоговый')) {
                        $finalTotalCol = $col;
                    } elseif ($gradeCol === null && str_contains($normalized, 'оценка')) {
                        $gradeCol = $col;
                    }
                }
            }
        }

        if ($headerRow === null || $studentNameCol === null) {
            throw new RuntimeException('Не удалось найти заголовок таблицы студентов в ведомости.');
        }

        $rows = [];
        for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
            $name = $this->normalizeStudentName($this->cellString($sheet, $row, $studentNameCol));
            if (! $this->isStudentRow($name)) {
                continue;
            }

            $module1 = $this->readNumeric($sheet, $row, $module1TheoryCol) + $this->readNumeric($sheet, $row, $module1PracticeCol);
            $module2 = $this->readNumeric($sheet, $row, $module2TheoryCol) + $this->readNumeric($sheet, $row, $module2PracticeCol);
            $mrsTotal = $this->readNumeric($sheet, $row, $mrsTotalCol);
            if ($mrsTotal <= 0) {
                $mrsTotal = $module1 + $module2;
            }
            $exam = $this->readNumeric($sheet, $row, $examCol);
            $finalTotal = $this->readNumeric($sheet, $row, $finalTotalCol);
            if ($finalTotal <= 0) {
                $finalTotal = min(100, $mrsTotal + $exam);
            }

            $rows[] = [
                'student_name' => $name,
                'group_name' => $groupName,
                'module1_score' => round(min(50, $module1), 2),
                'module2_score' => round(min(50, $module2), 2),
                'total_score' => round(min(100, $finalTotal), 2),
                'exam_score' => round(min(100, $exam), 2),
                'final_grade' => $this->normalizeGrade($this->cellString($sheet, $row, $gradeCol)),
                'raw_data' => [
                    'module1_theory' => $this->readNumeric($sheet, $row, $module1TheoryCol),
                    'module1_practice' => $this->readNumeric($sheet, $row, $module1PracticeCol),
                    'module2_theory' => $this->readNumeric($sheet, $row, $module2TheoryCol),
                    'module2_practice' => $this->readNumeric($sheet, $row, $module2PracticeCol),
                    'mrs_total' => $mrsTotal,
                    'exam_score' => $exam,
                    'final_total' => $finalTotal,
                    'teacher_name' => $teacherName,
                    'direction_code' => $directionCode,
                ],
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('Не найдено ни одной строки со студентом. Проверьте формат ведомости.');
        }

        return [
            'title' => trim(($discipline ? $discipline.' — ' : '').($groupName ?? '')),
            'discipline' => $discipline,
            'direction_code' => $directionCode,
            'group_name' => $groupName,
            'rows' => $rows,
        ];
    }

    private function rowAsLine(Worksheet $sheet, int $row, int $highestCol): string
    {
        $parts = [];
        for ($col = 1; $col <= $highestCol; $col++) {
            $value = trim($this->cellString($sheet, $row, $col));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        return implode(' ', $parts);
    }

    private function cellString(Worksheet $sheet, int $row, ?int $col): string
    {
        if ($col === null) {
            return '';
        }

        $cell = $sheet->getCell(new CellAddress(Coordinate::stringFromColumnIndex($col).$row));
        $value = $cell->getCalculatedValue();

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function readNumeric(Worksheet $sheet, int $row, ?int $col): float
    {
        if ($col === null) {
            return 0.0;
        }

        $cell = $sheet->getCell(new CellAddress(Coordinate::stringFromColumnIndex($col).$row));
        $value = $cell->getCalculatedValue();
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $normalized = str_replace(',', '.', (string) $value);

        return is_numeric($normalized) ? (float) $normalized : 0.0;
    }

    private function normalizeStudentName(string $value): string
    {
        $cleaned = preg_replace('/\s+/u', ' ', trim($value)) ?? '';
        $cleaned = preg_replace('/\b(АО|отчислен)\b/ui', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s+/u', ' ', trim($cleaned)) ?? '';

        return $cleaned;
    }

    private function isStudentRow(string $name): bool
    {
        if ($name === '' || mb_strlen($name) < 5) {
            return false;
        }

        if (preg_match('/^(идеальный студент|правила|фамилия|модуль|итог|оценка|лекции|практи)/ui', $name)) {
            return false;
        }

        return (bool) preg_match('/\p{L}/u', $name);
    }

    private function normalizeGrade(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function resolveStudent(string $fullName, ?string $groupName): ?User
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return null;
        }

        $parts = preg_split('/\s+/u', $fullName, 3);
        if (! is_array($parts) || $parts === []) {
            return null;
        }

        $lastName = $parts[0] ?? '';
        $firstName = $parts[1] ?? '';
        $secondName = $parts[2] ?? null;

        $user = User::query()
            ->where('role', UserRole::Student)
            ->where('last_name', $lastName)
            ->where('first_name', $firstName)
            ->where('second_name', $secondName)
            ->first();

        if (! $user) {
        $slug = Str::slug($fullName, '.');
        if ($slug === '') {
            $slug = 'student';
        }
            $email = $slug.'@import.local';
            $counter = 1;
            while (User::query()->where('email', $email)->exists()) {
                $counter++;
                $email = $slug.$counter.'@import.local';
            }

            $user = User::query()->create([
                'role' => UserRole::Student,
                'last_name' => $lastName !== '' ? $lastName : 'Неизвестно',
                'first_name' => $firstName !== '' ? $firstName : 'Студент',
                'second_name' => $secondName,
                'email' => $email,
                'password' => bcrypt(str()->random(32)),
                'is_active' => true,
            ]);
        }

        if ($groupName) {
            $group = StudentGroup::query()->where('name', $groupName)->first();
            if (! $group) {
                $group = StudentGroup::query()->create([
                    'name' => $groupName,
                    'education_form' => EducationForm::FullTime,
                    'course' => 1,
                ]);
            }

            StudentProfile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'group_id' => $group->id,
                    'student_id_number' => 'AUTO-'.$user->id,
                ],
            );
        }

        return $user;
    }
}
