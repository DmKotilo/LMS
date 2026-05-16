<?php

namespace Authorization\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use User\Enums\UserRole;
use User\Models\User;

class AuthService
{
    public function login(string $email, string $password): array
    {
        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Учётная запись деактивирована. Обратитесь к администратору.'],
            ]);
        }

        $this->assertRoleIntegrity($user);

        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        $user = $this->loadRelationsForRole($user);

        return [
            'token' => $token,
            'token_type' => 'Bearer',
            'default_path' => $user->defaultApiPath(),
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function loadRelationsForRole(User $user): User
    {
        return match ($user->role) {
            UserRole::Student => $user->load(['studentProfile.group']),
            UserRole::Teacher => $user->loadCount('gradebooksAsTeacher'),
            default => $user,
        };
    }

    private function assertRoleIntegrity(User $user): void
    {
        if ($user->isStudent() && ! $user->studentProfile()->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Профиль студента не настроен. Обратитесь к администратору.'],
            ]);
        }
    }
}
