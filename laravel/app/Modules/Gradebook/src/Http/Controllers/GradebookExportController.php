<?php

namespace Gradebook\Http\Controllers;

use App\Http\Controllers\Controller;
use Gradebook\Models\Gradebook;
use Gradebook\Services\GradebookExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookExportController extends Controller
{
    public function __construct(
        private readonly GradebookExportService $exportService,
    ) {}

    public function __invoke(Request $request, Gradebook $gradebook): StreamedResponse
    {
        $this->authorize('export', $gradebook);

        $format = $request->query('format', 'csv');
        if (! in_array($format, ['csv', 'json'], true)) {
            abort(422, 'Формат должен быть csv или json.');
        }

        return $this->exportService->export($gradebook, $format);
    }
}
