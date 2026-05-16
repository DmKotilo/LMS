<?php

namespace Authorization\Http\Requests\Tariffs;

use App\Http\Requests\FormRequest;
use Illuminate\Http\Request;

class TariffsFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "email" => ["email","nullable","max:255"],
            "phone" =>  ["nullable",'string',"max:20"],
            "name" => ['required','string','max:255'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (empty($this->input('phone')) && empty($this->input('email'))) {
                $validator->errors()->add('message', 'Необходимо указать хотя бы телефон или email.');
            }
        });
    }

}

