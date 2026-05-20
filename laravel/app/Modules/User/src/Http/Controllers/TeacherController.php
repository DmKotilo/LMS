<?php

namespace User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use User\Enums\UserRole;
use User\Http\Requests\StoreTeacherRequest;
use User\Http\Resources\UserResource;
use User\Models\User;

#[Group('Администрирование')]
#[Authenticated]
class TeacherController extends Controller
{
    #[Endpoint(
        title: 'Добавить преподавателя',
        description: 'Создаёт учетную запись преподавателя (роль teacher). Доступно только администратору.',
    )]
    #[BodyParam('full_name', 'string', 'ФИО преподавателя.', example: 'Докучаев Сергей Анатольевич')]
    #[BodyParam('login', 'string', 'Логин/email преподавателя.', example: 'teacher-new@institute.local')]
    #[BodyParam('password', 'string', 'Временный пароль.', example: 'teacher123')]
    #[ResponseFromFile('docs/responses/admin/teachers.store.201.json', 201)]
    #[ResponseFromFile('docs/responses/errors/401.json', 401)]
    #[ResponseFromFile('docs/responses/errors/403.json', 403)]
    #[ResponseFromFile('docs/responses/errors/422.json', 422)]
    public function store(StoreTeacherRequest $request): JsonResponse
    {
        [$lastName, $firstName, $secondName] = $this->splitFio($request->string('full_name')->toString());

        $teacher = User::query()->create([
            'role' => UserRole::Teacher,
            'last_name' => $lastName,
            'first_name' => $firstName,
            'second_name' => $secondName,
            'email' => $request->string('login')->toString(),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Преподаватель добавлен.',
            'data' => (new UserResource($teacher->loadCount('gradebooksAsTeacher')))->resolve(),
        ], 201);
    }

    /**
     * @return array{0:string,1:string,2:?string}
     */
    private function splitFio(string $fio): array
    {
        $parts = preg_split('/\s+/u', trim($fio), 3) ?: [];
        $last = $parts[0] ?? 'Неизвестно';
        $first = $parts[1] ?? 'Преподаватель';
        $second = $parts[2] ?? null;

        return [$last, $first, $second];
    }
}
