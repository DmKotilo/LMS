<?php

namespace User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use User\Enums\UserRole;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === UserRole::Administrator;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Укажите ФИО преподавателя.',
            'login.required' => 'Укажите логин преподавателя.',
            'login.email' => 'Логин должен быть валидным email.',
            'login.unique' => 'Пользователь с таким логином уже существует.',
            'password.required' => 'Укажите временный пароль.',
        ];
    }
}
