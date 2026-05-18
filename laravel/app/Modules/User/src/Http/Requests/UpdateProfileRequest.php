<?php

namespace User\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name' => ['sometimes', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'second_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.string' => 'Фамилия должна быть строкой.',
            'first_name.string' => 'Имя должно быть строкой.',
            'second_name.string' => 'Второе имя должно быть строкой.',
            'phone.max' => 'Телефон слишком длинный.',
        ];
    }
}
