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
                $student = $this->resolveStudent(
                    $row['student_name'],
                    $row['group_name'],
                );

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
    public function parseFromPath(string $absolutePath): array
    {
        $spreadsheet = IOFactory::load($absolutePath);

        return $this->parseWorksheet($spreadsheet->getActiveSheet());
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

        $studentNameCol = $this->resolveStudentNameColumn($sheet, $headerRow, $studentNameCol, $highestRow);

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

    private function resolveStudentNameColumn(Worksheet $sheet, int $headerRow, int $headerCol, int $highestRow): int
    {
        for ($row = $headerRow + 1; $row <= min($headerRow + 6, $highestRow); $row++) {
            $current = $this->normalizeStudentName($this->cellString($sheet, $row, $headerCol));
            $next = $this->normalizeStudentName($this->cellString($sheet, $row, $headerCol + 1));

            if ($this->isStudentRow($next) && ! $this->isStudentRow($current)) {
                return $headerCol + 1;
            }

            if ($this->isStudentRow($current)) {
                return $headerCol;
            }
        }

        return $headerCol;
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

        if (preg_match('/^\d+\./u', $name)) {
            return false;
        }

        $parts = preg_split('/\s+/u', $name);
        if (! is_array($parts) || count($parts) < 2 || count($parts) > 4) {
            return false;
        }

        if (! preg_match('/^\p{L}{2,}(?:-\p{L}+)*$/u', $parts[0])) {
            return false;
        }

        for ($i = 1; $i < count($parts); $i++) {
            if (! $this->isPersonNamePart($parts[$i])) {
                return false;
            }
        }

        return true;
    }

    private function isPersonNamePart(string $part): bool
    {
        if (preg_match('/^\p{L}{2,}(?:-\p{L}+)*$/u', $part)) {
            return true;
        }

        return (bool) preg_match('/^\p{L}\.(?:\p{L}\.)*$/u', $part);
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

        $nameParts = $this->parsePersonName($fullName);
        if ($nameParts === null) {
            return null;
        }

        $user = $this->findExistingStudent($nameParts, $groupName);

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
                'last_name' => $nameParts['last_name'] !== '' ? $nameParts['last_name'] : 'Неизвестно',
                'first_name' => $nameParts['first_name'] !== '' ? $nameParts['first_name'] : 'Студент',
                'second_name' => $nameParts['second_name'],
                'email' => $email,
                'password' => bcrypt(str()->random(32)),
                'is_active' => true,
            ]);
        }

        if ($groupName) {
            $group = StudentGroup::query()->where('name', $groupName)->first();
            if (! $group) {
                $group = StudentGroup::query()
                    ->get()
                    ->first(fn (StudentGroup $candidate) => $this->normalizeGroupName($candidate->name) === $this->normalizeGroupName($groupName));
            }
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
                    'student_id_number' => $user->studentProfile?->student_id_number ?? 'AUTO-'.$user->id,
                ],
            );
        }

        return $user;
    }

    /**
     * @return array{
     *   last_name: string,
     *   first_name: string,
     *   second_name: ?string,
     *   first_initial: ?string,
     *   second_initial: ?string,
     *   is_initials: bool
     * }|null
     */
    private function parsePersonName(string $fullName): ?array
    {
        $parts = preg_split('/\s+/u', trim($fullName), 3);
        if (! is_array($parts) || $parts === [] || $parts[0] === '') {
            return null;
        }

        $lastName = $parts[0];
        $firstPart = $parts[1] ?? '';
        $secondPart = $parts[2] ?? null;

        if ($firstPart === '') {
            return null;
        }

        if ($this->isInitialsPart($firstPart)) {
            $initials = $this->extractInitials($firstPart);

            return [
                'last_name' => $lastName,
                'first_name' => isset($initials[0]) ? $initials[0].'.' : '',
                'second_name' => isset($initials[1]) ? $initials[1].'.' : null,
                'first_initial' => $initials[0] ?? null,
                'second_initial' => $initials[1] ?? null,
                'is_initials' => true,
            ];
        }

        return [
            'last_name' => $lastName,
            'first_name' => $firstPart,
            'second_name' => $secondPart,
            'first_initial' => $this->initialLetter($firstPart),
            'second_initial' => $secondPart ? $this->initialLetter($secondPart) : null,
            'is_initials' => false,
        ];
    }

    /**
     * @param array{
     *   last_name: string,
     *   first_name: string,
     *   second_name: ?string,
     *   first_initial: ?string,
     *   second_initial: ?string,
     *   is_initials: bool
     * } $nameParts
     */
    private function findExistingStudent(array $nameParts, ?string $groupName): ?User
    {
        $candidates = User::query()
            ->where('role', UserRole::Student)
            ->where('last_name', $nameParts['last_name'])
            ->with('studentProfile.group')
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        if (! $nameParts['is_initials']) {
            $exact = $candidates->first(function (User $user) use ($nameParts) {
                return mb_strtolower($user->first_name) === mb_strtolower($nameParts['first_name'])
                    && mb_strtolower((string) $user->second_name) === mb_strtolower((string) ($nameParts['second_name'] ?? ''));
            });

            if ($exact) {
                return $exact;
            }
        }

        $byInitials = $candidates->filter(fn (User $user) => $this->studentMatchesByInitials($nameParts, $user));

        if ($byInitials->isEmpty()) {
            return null;
        }

        if ($groupName) {
            $normalizedGroup = $this->normalizeGroupName($groupName);
            $inGroup = $byInitials->filter(function (User $user) use ($normalizedGroup) {
                return $user->studentProfile?->group
                    && $this->normalizeGroupName($user->studentProfile->group->name) === $normalizedGroup;
            });

            if ($inGroup->isNotEmpty()) {
                return $this->pickBestStudentMatch($inGroup);
            }

            $sameDirection = $byInitials->filter(function (User $user) use ($normalizedGroup) {
                if (! $user->studentProfile?->group) {
                    return false;
                }

                $candidateGroup = $this->normalizeGroupName($user->studentProfile->group->name);

                return $this->groupsBelongToSameProgram($normalizedGroup, $candidateGroup);
            });

            if ($sameDirection->isNotEmpty()) {
                return $this->pickBestStudentMatch($sameDirection);
            }
        }

        return $this->pickBestStudentMatch($byInitials);
    }

    /**
     * @param array{
     *   first_initial: ?string,
     *   second_initial: ?string
     * } $nameParts
     */
    private function studentMatchesByInitials(array $nameParts, User $user): bool
    {
        if ($nameParts['first_initial'] === null) {
            return false;
        }

        if ($this->initialLetter($user->first_name) !== $nameParts['first_initial']) {
            return false;
        }

        if ($nameParts['second_initial'] !== null) {
            if ($user->second_name === null || $user->second_name === '') {
                return false;
            }

            if ($this->initialLetter($user->second_name) !== $nameParts['second_initial']) {
                return false;
            }
        }

        return true;
    }

    private function pickBestStudentMatch($candidates): User
    {
        /** @var User $user */
        return $candidates
            ->sortByDesc(fn (User $user) => mb_strlen($user->first_name))
            ->first();
    }

    private function normalizeGroupName(string $name): string
    {
        $normalized = preg_replace('/[\s\-_]/u', '', mb_strtoupper(trim($name))) ?? mb_strtoupper(trim($name));

        return $normalized;
    }

    private function groupsBelongToSameProgram(string $importGroup, string $candidateGroup): bool
    {
        if ($importGroup === $candidateGroup) {
            return true;
        }

        $importPrefix = preg_replace('/\d+/u', '', $importGroup) ?? $importGroup;
        $candidatePrefix = preg_replace('/\d+/u', '', $candidateGroup) ?? $candidateGroup;

        return $importPrefix !== '' && $importPrefix === $candidatePrefix;
    }

    private function extractInitials(string $part): array
    {
        preg_match_all('/\p{L}/u', $part, $matches);

        return array_map(
            fn (string $letter) => mb_strtoupper($letter),
            $matches[0] ?? [],
        );
    }

    private function initialLetter(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return mb_strtoupper(mb_substr(trim($value), 0, 1));
    }

    private function isInitialsPart(string $part): bool
    {
        return (bool) preg_match('/^\p{L}\.(?:\p{L}\.)*$/u', $part);
    }
}
