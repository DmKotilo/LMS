<?php

namespace Connect\Http\Controllers;

use App\Http\Controllers\Controller;
use Connect\Http\Requests\CheckConnectRequest;
use Connect\Models\CheckConnection;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Служебные')]
#[Unauthenticated]
class CheckConnectController extends Controller
{
    #[Endpoint(
        title: 'Проверка соединения',
        description: 'Сохраняет отметку проверки доступности API.',
    )]
    #[BodyParam('date_current', 'string', 'Дата и время проверки соединения.', example: '2026-05-16T12:00:00')]
    #[ResponseFromFile('docs/responses/connect/check.200.json')]
    public function check(CheckConnectRequest $request): JsonResponse
    {
        $checkCreate = CheckConnection::create($request->validated());

        return response()->json($checkCreate);
    }
}
