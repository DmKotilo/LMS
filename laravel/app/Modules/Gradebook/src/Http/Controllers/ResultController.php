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
