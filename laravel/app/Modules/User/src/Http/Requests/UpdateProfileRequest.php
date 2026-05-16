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
        $user = $this->user();

        $rules = [
            'phone' => ['sometimes', 'nullable', 'string', 'max:32'],
            'second_name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];

        if ($user?->isAdministrator()) {
            $rules['last_name'] = ['sometimes', 'string', 'max:255'];
            $rules['first_name'] = ['sometimes', 'string', 'max:255'];
        }

        return $rules;
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
