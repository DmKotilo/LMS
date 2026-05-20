<?php

namespace Gradebook\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use User\Enums\UserRole;

class ImportGradebookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && in_array($user->role, [UserRole::Administrator, UserRole::Teacher], true);
    }

    public function rules(): array
    {
        $rules = [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'semester' => ['required', 'string', 'max:50'],
            'academic_year' => ['required', 'string', 'max:20'],
        ];

        if ($this->user()?->isAdministrator()) {
            $rules['teacher_id'] = ['nullable', 'integer', 'exists:users,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Выберите файл ведомости.',
            'file.mimes' => 'Поддерживаются только файлы .xls и .xlsx.',
            'file.max' => 'Размер файла не должен превышать 10 МБ.',
            'semester.required' => 'Выберите семестр.',
            'academic_year.required' => 'Выберите учебный год.',
            'teacher_id.exists' => 'Выбранный преподаватель не найден.',
        ];
    }
}
