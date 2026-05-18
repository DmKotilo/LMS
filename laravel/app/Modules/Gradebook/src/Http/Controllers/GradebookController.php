<?php

namespace Gradebook\Http\Controllers;

use App\Http\Controllers\Controller;
use Gradebook\Http\Resources\GradebookDetailResource;
use Gradebook\Http\Resources\GradebookResource;
use Gradebook\Models\Gradebook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Для преподавателя и администратора', 'Роли `teacher` и `administrator`. Студент получит 403.')]
#[Authenticated]
class GradebookController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Gradebook::class, 'gradebook');
    }

    #[Endpoint(
        title: 'Список ведомостей',
        description: 'Преподаватель видит только свои ведомости; администратор — все.',
    )]
    #[QueryParam('search', 'string', 'Поиск по названию, дисциплине или группе.', required: false, example: 'Математика')]
    #[QueryParam('discipline', 'string', 'Фильтр по дисциплине.', required: false, example: 'Физика')]
    #[QueryParam('group_name', 'string', 'Фильтр по группе.', required: false, example: 'ИВТ-21')]
    #[QueryParam('semester', 'string', 'Фильтр по семестру.', required: false, example: '2025-осень')]
    #[QueryParam('teacher_id', 'integer', 'Фильтр по преподавателю (ID).', required: false, example: 1)]
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
            ->filter($request->only(['search', 'discipline', 'group_name', 'semester', 'teacher_id']))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 15));

        return GradebookResource::collection($gradebooks);
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
