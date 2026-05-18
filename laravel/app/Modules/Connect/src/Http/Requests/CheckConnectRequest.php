<?php

namespace Connect\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckConnectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date_current' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_current.required' => 'Укажите дату проверки.',
        ];
    }
}
