<?php

namespace User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Укажите текущий пароль.',
            'password.required' => 'Укажите новый пароль.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'password.min' => 'Пароль должен содержать не менее 8 символов.',
        ];
    }
}
