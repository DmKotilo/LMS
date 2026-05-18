<?php

namespace Gradebook\Http\Controllers;

use App\Http\Controllers\Controller;
use Gradebook\Models\Gradebook;
use Gradebook\Services\GradebookExportService;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Group('Для преподавателя и администратора', 'Роли `teacher` и `administrator`. Студент получит 403.')]
#[Authenticated]
class GradebookExportController extends Controller
{
    public function __construct(
        private readonly GradebookExportService $exportService,
    ) {}

    #[Endpoint(title: 'Экспорт ведомости')]
    #[UrlParam('gradebook', 'integer', 'ID ведомости.', example: 1)]
    #[QueryParam('format', 'string', 'Формат файла: csv или json.', required: false, example: 'csv')]
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
