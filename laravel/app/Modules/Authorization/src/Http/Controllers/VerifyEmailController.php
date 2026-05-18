<?php

namespace Authorization\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Knuckles\Scribe\Attributes\UrlParam;
use User\Http\Resources\UserResource;
use User\Models\User;

#[Group('Общее')]
class VerifyEmailController extends Controller
{
    #[Unauthenticated]
    #[Endpoint(
        title: 'Подтверждение email',
        description: 'Ссылка из письма (signed URL). При смене почты переносит `new_email` в `email` и сбрасывает `new_email`.',
    )]
    #[UrlParam('id', 'integer', 'ID пользователя.', example: 1)]
    #[UrlParam('hash', 'string', 'SHA1 хеш email для подтверждения.', example: 'abc...')]
    #[ResponseFromFile('docs/responses/profile/email-verify.200.json')]
    #[ResponseFromFile('docs/responses/errors/403.json', status: 403)]
    public function __invoke(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Недействительная ссылка подтверждения.');
        }

        if ($user->new_email) {
            $user->email = $user->new_email;
            $user->new_email = null;
        }

        if (! $user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        } else {
            $user->save();
        }

        return response()->json([
            'message' => 'Email успешно подтверждён.',
            'user' => new UserResource($user->fresh(['studentProfile.group'])),
        ]);
    }
}
