<?php

namespace Authorization\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class RegisterFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        return [
            'phone' => ['nullable', 'required_without:email', 'unique:users', 'numeric', "regex:/^([0-9\s\-\+\(\)]*)$/", 'digits_between:8,16'],
            'email' => ['nullable', 'required_without:phone', 'unique:users', 'email'],
          /*  'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'min:3', 'max:255'],
            'middle_name' => ['nullable', 'string', 'min:3', 'max:255'],*/
            // "birthday" => ["required","date","date_format:Y-m-d"],
/*            'code' => ['required', 'numeric', 'regex:/^([0-9]*)$/', 'digits:' . config('code_verification.code_length')],
            'password' => ['required', 'string', 'min:6', 'max:255', 'same:password_confirmation', 'required_with:password_confirmation'],
            'password_confirmation' => ['required', 'string', 'min:6'],*/
        ];
    }

    public function messages()
    {
        return [
            'email.required_without' => 'Если нет телефона то почта обязательна',
            'phone.required_without' => 'Если нет почты, то телефон обязателен',
        ];
    }
}
