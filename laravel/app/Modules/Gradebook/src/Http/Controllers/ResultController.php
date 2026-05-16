<?php

namespace Gradebook\Http\Controllers;

use App\Http\Controllers\Controller;
use Gradebook\Http\Resources\GradebookRowResource;
use Gradebook\Models\GradebookRow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ResultController extends Controller
{
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
}
