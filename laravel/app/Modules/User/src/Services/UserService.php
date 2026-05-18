<?php

namespace User\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use User\Models\User;

class UserService
{
    public function updateProfile(User $user, array $data): User
    {
        $allowed = $this->editableProfileFields();

        $payload = array_intersect_key($data, array_flip($allowed));

        if ($payload === []) {
            throw ValidationException::withMessages([
                'profile' => ['Нет полей, доступных для редактирования.'],
            ]);
        }

        $user->update($payload);

        return $user->fresh(['studentProfile.group']);
    }

    public function updatePassword(User $user, string $currentPassword, string $newPassword): void
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Текущий пароль указан неверно.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $user->tokens()->where('id', '!=', $user->currentAccessToken()?->id)->delete();
    }

    /**
     * @return list<string>
     */
    private function editableProfileFields(): array
    {
        return [
            'last_name',
            'first_name',
            'second_name',
            'phone',
        ];
    }
}
