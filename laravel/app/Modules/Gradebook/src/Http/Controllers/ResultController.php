<?php

namespace Gradebook\Http\Controllers;

use App\Http\Controllers\Controller;
use Gradebook\Http\Resources\GradebookRowResource;
use Gradebook\Models\GradebookRow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use User\Http\Resources\UserResource;

#[Group('Для студента', 'Требуется роль `student` и настроенный `student_profile`. Иначе — 403.')]
#[Authenticated]
class ResultController extends Controller
{
    #[Endpoint(
        title: 'Результаты',
        description: 'Список оценок текущего студента. Доступно только при `user.role = student`.',
    )]
    #[QueryParam('semester', 'string', 'Фильтр по семестру.', required: false, example: '2025-осень')]
    #[QueryParam('per_page', 'integer', 'Количество записей на странице.', required: false, example: 20)]
    #[ResponseFromFile('docs/responses/student/results.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'object[]', 'Список оценок студента.')]
    #[ResponseField('data[].id', 'integer', 'ID строки ведомости.')]
    #[ResponseField('data[].student_name', 'string', 'ФИО студента.')]
    #[ResponseField('data[].group_name', 'string', 'Группа.')]
    #[ResponseField('data[].semester', 'string', 'Семестр.')]
    #[ResponseField('data[].module1_score', 'string', 'Балл за модуль 1.')]
    #[ResponseField('data[].module2_score', 'string', 'Балл за модуль 2.')]
    #[ResponseField('data[].module1_theory', 'integer', 'Теория модуля 1.')]
    #[ResponseField('data[].module1_practice', 'integer', 'Практика модуля 1.')]
    #[ResponseField('data[].module2_theory', 'integer', 'Теория модуля 2.')]
    #[ResponseField('data[].module2_practice', 'integer', 'Практика модуля 2.')]
    #[ResponseField('data[].mrs_score', 'integer', 'Сумма МРС.')]
    #[ResponseField('data[].exam_score', 'string', 'Балл за экзамен.')]
    #[ResponseField('data[].total_score', 'string', 'Итоговый балл.')]
    #[ResponseField('data[].final_grade', 'string', 'Итоговая оценка.')]
    #[ResponseField('data[].gradebook', 'object', 'Краткие данные ведомости.')]
    #[ResponseField('data[].gradebook.id', 'integer', 'ID ведомости.')]
    #[ResponseField('data[].gradebook.title', 'string', 'Название ведомости.')]
    #[ResponseField('data[].gradebook.discipline', 'string', 'Дисциплина.')]
    #[ResponseField('data[].gradebook.direction_code', 'string', 'Код направления.')]
    #[ResponseField('data[].gradebook.group_name', 'string', 'Группа.')]
    #[ResponseField('data[].gradebook.semester', 'string', 'Семестр.')]
    #[ResponseField('data[].gradebook.academic_year', 'string', 'Учебный год.')]
    #[ResponseField('data[].gradebook.teacher', 'object', 'Преподаватель.')]
    #[ResponseField('data[].gradebook.teacher.id', 'integer', 'ID преподавателя.')]
    #[ResponseField('data[].gradebook.teacher.full_name', 'string', 'ФИО преподавателя.')]
    #[ResponseField('data[].gradebook.original_filename', 'string|null', 'Имя загруженного файла.', required: false)]
    #[ResponseField('data[].gradebook.uploaded_at', 'string', 'Дата загрузки (ISO 8601).')]
    #[ResponseField('links', 'object', 'Ссылки пагинации.')]
    #[ResponseField('links.first', 'string', 'URL первой страницы.')]
    #[ResponseField('links.last', 'string', 'URL последней страницы.')]
    #[ResponseField('links.prev', 'string|null', 'URL предыдущей страницы.', required: false)]
    #[ResponseField('links.next', 'string|null', 'URL следующей страницы.', required: false)]
    #[ResponseField('meta', 'object', 'Метаданные пагинации.')]
    #[ResponseField('meta.current_page', 'integer', 'Текущая страница.')]
    #[ResponseField('meta.from', 'integer', 'Номер первой записи на странице.')]
    #[ResponseField('meta.last_page', 'integer', 'Всего страниц.')]
    #[ResponseField('meta.path', 'string', 'Базовый URL списка.')]
    #[ResponseField('meta.per_page', 'integer', 'Записей на странице.')]
    #[ResponseField('meta.to', 'integer', 'Номер последней записи на странице.')]
    #[ResponseField('meta.total', 'integer', 'Всего записей.')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', GradebookRow::class);

        $rows = GradebookRow::query()
            ->forStudent($request->user())
            ->with(['gradebook.teacher'])
            ->filterSemester($request->query('semester'))
            ->join('gradebooks', 'gradebooks.id', '=', 'gradebook_rows.gradebook_id')
            ->orderBy('gradebooks.semester')
            ->orderBy('gradebooks.discipline')
            ->select('gradebook_rows.*')
            ->paginate($request->integer('per_page', 20));

        return GradebookRowResource::collection($rows);
    }

    #[Endpoint(
        title: 'Семестры',
        description: 'Список семестров с результатами. Доступно только при `user.role = student`.',
    )]
    #[ResponseFromFile('docs/responses/student/semesters.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'string[]', 'Список семестров, в которых есть результаты.')]
    #[ResponseField('data[]', 'string', 'Название семестра.', example: '1')]
    public function semesters(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GradebookRow::class);

        $semesters = GradebookRow::query()
            ->forStudent($request->user())
            ->join('gradebooks', 'gradebooks.id', '=', 'gradebook_rows.gradebook_id')
            ->selectRaw('COALESCE(NULLIF(gradebook_rows.semester, \'\'), gradebooks.semester) as semester')
            ->whereNotNull('semester')
            ->where('semester', '!=', '')
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester');

        return response()->json([
            'data' => $semesters->values(),
        ]);
    }

    #[Endpoint(
        title: 'Сводка студента',
        description: 'Карточка студента + агрегированные показатели (кол-во дисциплин, средний МРС, средний итог).',
    )]
    #[ResponseFromFile('docs/responses/student/summary.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'object', 'Данные ответа.')]
    #[ResponseField('data.student', 'object', 'Карточка текущего студента.')]
    #[ResponseField('data.student.id', 'integer', 'ID пользователя.')]
    #[ResponseField('data.student.role', 'string', 'Роль: student.')]
    #[ResponseField('data.student.role_label', 'string', 'Название роли.')]
    #[ResponseField('data.student.first_name', 'string', 'Имя.')]
    #[ResponseField('data.student.second_name', 'string', 'Отчество.')]
    #[ResponseField('data.student.last_name', 'string', 'Фамилия.')]
    #[ResponseField('data.student.full_name', 'string', 'ФИО.')]
    #[ResponseField('data.student.email', 'string', 'Email.')]
    #[ResponseField('data.student.email_verified_at', 'string|null', 'Дата подтверждения email (ISO 8601).', required: false)]
    #[ResponseField('data.student.phone', 'string|null', 'Телефон.', required: false)]
    #[ResponseField('data.student.is_active', 'boolean', 'Активен ли аккаунт.')]
    #[ResponseField('data.student.default_path', 'string', 'Стартовый путь API для роли.')]
    #[ResponseField('data.student.student_profile', 'object', 'Профиль студента.')]
    #[ResponseField('data.student.student_profile.student_id_number', 'string', 'Номер студенческого билета.')]
    #[ResponseField('data.student.student_profile.group', 'object', 'Учебная группа.')]
    #[ResponseField('data.student.student_profile.group.id', 'integer', 'ID группы.')]
    #[ResponseField('data.student.student_profile.group.name', 'string', 'Название группы.')]
    #[ResponseField('data.student.student_profile.group.education_form', 'string', 'Форма обучения: full_time | part_time | distance.')]
    #[ResponseField('data.student.student_profile.group.education_form_label', 'string', 'Название формы обучения.')]
    #[ResponseField('data.student.student_profile.group.course', 'integer', 'Курс.')]
    #[ResponseField('data.student.created_at', 'string', 'Дата создания (ISO 8601).')]
    #[ResponseField('data.student.updated_at', 'string', 'Дата обновления (ISO 8601).')]
    #[ResponseField('data.stats', 'object', 'Агрегированные показатели.')]
    #[ResponseField('data.stats.disciplines_count', 'integer', 'Количество дисциплин с результатами.')]
    #[ResponseField('data.stats.avg_mrs', 'number', 'Средний балл МРС.')]
    #[ResponseField('data.stats.avg_total', 'number', 'Средний итоговый балл.')]
    public function summary(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GradebookRow::class);

        $student = $request->user()->loadMissing(['studentProfile.group']);

        $stats = GradebookRow::query()
            ->forStudent($student)
            ->selectRaw('COUNT(DISTINCT gradebook_id) as disciplines_count')
            ->selectRaw('AVG(COALESCE((raw_data->>\'mrs_total\')::numeric, module1_score + module2_score)) as avg_mrs')
            ->selectRaw('AVG(total_score) as avg_total')
            ->first();

        return response()->json([
            'data' => [
                'student' => (new UserResource($student))->resolve(),
                'stats' => [
                    'disciplines_count' => (int) ($stats->disciplines_count ?? 0),
                    'avg_mrs' => round((float) ($stats->avg_mrs ?? 0), 2),
                    'avg_total' => round((float) ($stats->avg_total ?? 0), 2),
                ],
            ],
        ]);
    }
}
