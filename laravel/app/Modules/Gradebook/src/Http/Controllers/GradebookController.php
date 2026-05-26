<?php

namespace Gradebook\Http\Controllers;

use App\Http\Controllers\Controller;
use Gradebook\Http\Resources\GradebookDetailResource;
use Gradebook\Http\Resources\GradebookResource;
use Gradebook\Http\Requests\ImportGradebookRequest;
use Gradebook\Models\Gradebook;
use Gradebook\Services\GradebookImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;
use User\Enums\UserRole;
use User\Models\User;

#[Group('Для преподавателя и администратора', 'Роли `teacher` и `administrator`. Студент получит 403.')]
#[Authenticated]
class GradebookController extends Controller
{
    public function __construct(
        private readonly GradebookImportService $importService,
    )
    {
        $this->authorizeResource(Gradebook::class, 'gradebook');
    }

    #[Endpoint(
        title: 'Загрузка ведомости XLSX',
        description: 'Загружает и парсит xlsx/xls ведомость МРС: читает шапку (дисциплина, группа, направление) и таблицу студентов. `semester` и `academic_year` берутся из body запроса.',
    )]
    #[BodyParam('file', 'file', 'Файл ведомости .xlsx/.xls', example: 'No-example')]
    #[BodyParam('semester', 'string', 'Семестр из формы загрузки.', example: '1')]
    #[BodyParam('academic_year', 'string', 'Учебный год из формы загрузки.', example: '2025/2026')]
    #[BodyParam('teacher_id', 'integer', 'ID преподавателя (обязательно для администратора).', required: false, example: 2)]
    #[ResponseFromFile('docs/responses/gradebooks/import.201.json', 201)]
    #[ResponseFromFile('docs/responses/errors/401.json', 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', 403)]
    #[ResponseFromFile('docs/responses/errors/422.json', 422)]
    public function import(ImportGradebookRequest $request): JsonResponse
    {
        $this->authorize('create', Gradebook::class);

        $gradebook = $this->importService->importFromUploadedFile(
            $request->file('file'),
            $request->user(),
            $request->string('semester')->toString(),
            $request->string('academic_year')->toString(),
            $request->integer('teacher_id') ?: null,
        );

        return (new GradebookResource($gradebook))
            ->response()
            ->setStatusCode(201);
    }

    #[Endpoint(
        title: 'Список ведомостей',
        description: 'Преподаватель видит только свои ведомости; администратор — все.',
    )]
    #[QueryParam('search', 'string', 'Поиск по названию, дисциплине или группе.', required: false, example: 'Математика')]
    #[QueryParam('discipline', 'string', 'Фильтр по дисциплине.', required: false, example: 'Физика')]
    #[QueryParam('group_name', 'string', 'Фильтр по группе.', required: false, example: 'ИВТ-21')]
    #[QueryParam('semester', 'string', 'Фильтр по семестру.', required: false, example: '2025-осень')]
    #[QueryParam('academic_year', 'string', 'Фильтр по учебному году.', required: false, example: '2025/2026')]
    #[QueryParam('teacher_id', 'integer', 'Фильтр по преподавателю (ID).', required: false, example: 1)]
    #[QueryParam('sort', 'string', 'Сортировка: newest, oldest, discipline_asc, discipline_desc, group_asc, group_desc.', required: false, example: 'newest')]
    #[QueryParam('per_page', 'integer', 'Количество записей на странице.', required: false, example: 15)]
    #[ResponseFromFile('docs/responses/gradebooks/index.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'object[]', 'Список ведомостей.')]
    #[ResponseField('data[].id', 'integer', 'ID ведомости.')]
    #[ResponseField('data[].title', 'string', 'Название ведомости.')]
    #[ResponseField('data[].discipline', 'string', 'Дисциплина.')]
    #[ResponseField('data[].direction_code', 'string', 'Код направления.')]
    #[ResponseField('data[].group_name', 'string', 'Группа.')]
    #[ResponseField('data[].semester', 'string', 'Семестр.')]
    #[ResponseField('data[].academic_year', 'string', 'Учебный год.')]
    #[ResponseField('data[].teacher', 'object', 'Преподаватель.')]
    #[ResponseField('data[].teacher.id', 'integer', 'ID преподавателя.')]
    #[ResponseField('data[].teacher.full_name', 'string', 'ФИО преподавателя.')]
    #[ResponseField('data[].original_filename', 'string|null', 'Имя загруженного файла.', required: false)]
    #[ResponseField('data[].rows_count', 'integer', 'Количество строк (студентов).')]
    #[ResponseField('data[].uploaded_at', 'string', 'Дата загрузки (ISO 8601).')]
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
        $gradebooks = Gradebook::query()
            ->forUser($request->user())
            ->with('teacher')
            ->withCount('rows')
            ->filter($request->only(['search', 'discipline', 'group_name', 'semester', 'academic_year', 'teacher_id']))
            ->applySort($request->string('sort')->toString())
            ->paginate($request->integer('per_page', 15));

        return GradebookResource::collection($gradebooks);
    }

    #[Endpoint(
        title: 'Список преподавателей',
        description: 'Все активные преподаватели для выбора при загрузке ведомости (`teacher_id` в `POST /api/gradebooks/import`). Администратор выбирает из списка; преподаватель загружает ведомость от своего имени без `teacher_id`.',
    )]
    #[ResponseFromFile('docs/responses/gradebooks/teachers.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'object[]', 'Список преподавателей.')]
    #[ResponseField('data[].id', 'integer', 'ID преподавателя.')]
    #[ResponseField('data[].full_name', 'string', 'ФИО.')]
    #[ResponseField('data[].email', 'string', 'Email (логин).')]
    public function teachers(Request $request): JsonResponse
    {
        $this->authorize('create', Gradebook::class);

        $teachers = User::query()
            ->where('role', UserRole::Teacher)
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('second_name')
            ->get(['id', 'last_name', 'first_name', 'second_name', 'email'])
            ->map(fn (User $teacher) => [
                'id' => $teacher->id,
                'full_name' => $teacher->fullName(),
                'email' => $teacher->email,
            ])
            ->values();

        return response()->json([
            'data' => $teachers,
        ]);
    }

    #[Endpoint(
        title: 'Опции фильтров ведомостей',
        description: 'Возвращает списки семестров, учебных годов, групп, дисциплин и преподавателей для формы поиска/фильтрации.',
    )]
    #[ResponseFromFile('docs/responses/gradebooks/filters.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'object', 'Опции для фильтров.')]
    #[ResponseField('data.semesters', 'string[]', 'Доступные семестры.')]
    #[ResponseField('data.semesters[]', 'string', 'Семестр.', example: '1')]
    #[ResponseField('data.academic_years', 'string[]', 'Доступные учебные годы.')]
    #[ResponseField('data.academic_years[]', 'string', 'Учебный год.', example: '2025/2026')]
    #[ResponseField('data.groups', 'string[]', 'Доступные группы.')]
    #[ResponseField('data.groups[]', 'string', 'Группа.', example: 'ИВТ-401')]
    #[ResponseField('data.disciplines', 'string[]', 'Доступные дисциплины.')]
    #[ResponseField('data.disciplines[]', 'string', 'Дисциплина.')]
    #[ResponseField('data.teachers', 'object[]', 'Преподаватели.')]
    #[ResponseField('data.teachers[].id', 'integer', 'ID преподавателя.')]
    #[ResponseField('data.teachers[].full_name', 'string', 'ФИО преподавателя.')]
    #[ResponseField('data.sort_options', 'object[]', 'Варианты сортировки.')]
    #[ResponseField('data.sort_options[].value', 'string', 'Код сортировки: newest, oldest, discipline_asc, discipline_desc, group_asc, group_desc.')]
    #[ResponseField('data.sort_options[].label', 'string', 'Подпись для UI.')]
    public function filterOptions(Request $request): JsonResponse
    {
        $base = Gradebook::query()->forUser($request->user());

        $semesters = (clone $base)
            ->whereNotNull('semester')
            ->where('semester', '!=', '')
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester')
            ->values();

        $academicYears = (clone $base)
            ->whereNotNull('academic_year')
            ->where('academic_year', '!=', '')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year')
            ->values();

        $groups = (clone $base)
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name')
            ->values();

        $disciplines = (clone $base)
            ->whereNotNull('discipline')
            ->where('discipline', '!=', '')
            ->distinct()
            ->orderBy('discipline')
            ->pluck('discipline')
            ->values();

        $teachers = (clone $base)
            ->with('teacher:id,last_name,first_name,second_name')
            ->get()
            ->pluck('teacher')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($teacher) => $teacher->fullName())
            ->values()
            ->map(fn ($teacher) => [
                'id' => $teacher->id,
                'full_name' => $teacher->fullName(),
            ])
            ->values();

        return response()->json([
            'data' => [
                'semesters' => $semesters,
                'academic_years' => $academicYears,
                'groups' => $groups,
                'disciplines' => $disciplines,
                'teachers' => $teachers,
                'sort_options' => [
                    ['value' => 'newest', 'label' => 'Сначала новые'],
                    ['value' => 'oldest', 'label' => 'Сначала старые'],
                    ['value' => 'discipline_asc', 'label' => 'Дисциплина А-Я'],
                    ['value' => 'discipline_desc', 'label' => 'Дисциплина Я-А'],
                    ['value' => 'group_asc', 'label' => 'Группа А-Я'],
                    ['value' => 'group_desc', 'label' => 'Группа Я-А'],
                ],
            ],
        ]);
    }

    #[Endpoint(title: 'Просмотр ведомости')]
    #[UrlParam('gradebook', 'integer', 'ID ведомости.', example: 1)]
    #[ResponseFromFile('docs/responses/gradebooks/show.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    #[ResponseField('data', 'object', 'Данные ведомости.')]
    #[ResponseField('data.id', 'integer', 'ID ведомости.')]
    #[ResponseField('data.title', 'string', 'Название ведомости.')]
    #[ResponseField('data.discipline', 'string', 'Дисциплина.')]
    #[ResponseField('data.direction_code', 'string', 'Код направления.')]
    #[ResponseField('data.group_name', 'string', 'Группа.')]
    #[ResponseField('data.semester', 'string', 'Семестр.')]
    #[ResponseField('data.academic_year', 'string', 'Учебный год.')]
    #[ResponseField('data.teacher', 'object', 'Преподаватель.')]
    #[ResponseField('data.teacher.id', 'integer', 'ID преподавателя.')]
    #[ResponseField('data.teacher.full_name', 'string', 'ФИО преподавателя.')]
    #[ResponseField('data.original_filename', 'string|null', 'Имя загруженного файла.', required: false)]
    #[ResponseField('data.rows_count', 'integer', 'Количество строк (студентов).')]
    #[ResponseField('data.uploaded_at', 'string', 'Дата загрузки (ISO 8601).')]
    #[ResponseField('data.rows', 'object[]', 'Строки ведомости (оценки студентов).')]
    #[ResponseField('data.rows[].id', 'integer', 'ID строки ведомости.')]
    #[ResponseField('data.rows[].student_name', 'string', 'ФИО студента.')]
    #[ResponseField('data.rows[].group_name', 'string', 'Группа.')]
    #[ResponseField('data.rows[].semester', 'string', 'Семестр.')]
    #[ResponseField('data.rows[].module1_score', 'string', 'Балл за модуль 1.')]
    #[ResponseField('data.rows[].module2_score', 'string', 'Балл за модуль 2.')]
    #[ResponseField('data.rows[].module1_theory', 'integer', 'Теория модуля 1.')]
    #[ResponseField('data.rows[].module1_practice', 'integer', 'Практика модуля 1.')]
    #[ResponseField('data.rows[].module2_theory', 'integer', 'Теория модуля 2.')]
    #[ResponseField('data.rows[].module2_practice', 'integer', 'Практика модуля 2.')]
    #[ResponseField('data.rows[].mrs_score', 'integer', 'Сумма МРС.')]
    #[ResponseField('data.rows[].exam_score', 'string', 'Балл за экзамен.')]
    #[ResponseField('data.rows[].total_score', 'string', 'Итоговый балл.')]
    #[ResponseField('data.rows[].final_grade', 'string', 'Итоговая оценка.')]
    public function show(Gradebook $gradebook): GradebookDetailResource
    {
        $gradebook->load([
            'teacher',
            'rows' => fn ($q) => $q->orderBy('student_name'),
        ]);

        return new GradebookDetailResource($gradebook);
    }

    #[Endpoint(
        title: 'Удаление ведомости',
        description: 'Только роль `administrator`.',
    )]
    #[UrlParam('gradebook', 'integer', 'ID ведомости.', example: 1)]
    #[ResponseFromFile('docs/responses/gradebooks/destroy.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    public function destroy(Gradebook $gradebook): JsonResponse
    {
        if ($gradebook->storage_path) {
            Storage::disk('local')->delete($gradebook->storage_path);
        }

        $gradebook->delete();

        return response()->json([
            'message' => 'Ведомость удалена.',
        ]);
    }
}
