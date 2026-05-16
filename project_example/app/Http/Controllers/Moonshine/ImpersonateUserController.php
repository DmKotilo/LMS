<?php

namespace App\Http\Controllers\Moonshine;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MoonShine\Laravel\Models\MoonshineUser;
use Throwable;
use User\Models\User;

class ImpersonateUserController extends Controller
{
    private const IMPERSONATOR_ROLE = 'Имперсонация';

    /** Email имперсонатора: .local — локаль, .ru — прод (совпадает с MoonshineSeeder) */
    private const IMPERSONATOR_EMAILS = [
        'impersonator@primefoods.local',
        'impersonator@primefoods.ru',
    ];

    public function __invoke(Request $request, User $user): RedirectResponse
    {
        /** @var MoonshineUser|null $admin */
        $admin = Auth::guard('moonshine')->user();

        if (! $admin || ! $this->isAllowed($admin)) {
            abort(403);
        }

        try {
            // Важно: оба guard'а (moonshine и web) хранятся в одной Laravel-сессии.
            // При логине Laravel может регенерировать session id — поэтому сохраняем admin id и восстанавливаем его.
            $moonshineId = Auth::guard('moonshine')->id();

            // Логинимся на web-guard под выбранным клиентом (Laravel сам регенерирует session id)
            Auth::guard('web')->login($user);

            // На всякий случай восстанавливаем moonshine-guard после возможной регенерации сессии
            if ($moonshineId) {
                Auth::guard('moonshine')->loginUsingId($moonshineId);
            }

            logger()->notice('MoonShine: impersonate user', [
                'moonshine_user_id' => $admin->id,
                'impersonated_user_id' => $user->getKey(),
            ]);
        } catch (Throwable $e) {
            logger()->error('MoonShine: impersonate failed', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            throw $e;
        }

        // Перекидываем на главную сайта
        return redirect('/');
    }

    private function isAllowed(MoonshineUser $admin): bool
    {
        $email = $admin->email;
        if (is_string($email) && in_array($email, self::IMPERSONATOR_EMAILS, true)) {
            return true;
        }

        return (self::IMPERSONATOR_ROLE !== '')
            && ($admin->moonshineUserRole?->name === self::IMPERSONATOR_ROLE);
    }
}

