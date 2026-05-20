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
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

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
    #[BodyParam('teacher_id', 'integer', 'ID преподавателя (только для администратора).', required: false, example: 2)]
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
        title: 'Опции фильтров ведомостей',
        description: 'Возвращает списки семестров, учебных годов, групп, дисциплин и преподавателей для формы поиска/фильтрации.',
    )]
    #[ResponseFromFile('docs/responses/gradebooks/filters.200.json')]
    #[ResponseFromFile('docs/responses/errors/401.json', status: 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
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
