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

class GradebookController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Gradebook::class, 'gradebook');
    }

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

    public function show(Gradebook $gradebook): GradebookDetailResource
    {
        $gradebook->load([
            'teacher',
            'rows' => fn ($q) => $q->orderBy('student_name'),
        ]);

        return new GradebookDetailResource($gradebook);
    }

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
