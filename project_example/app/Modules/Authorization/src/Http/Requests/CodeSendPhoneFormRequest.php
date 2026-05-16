<?php

namespace Authorization\Http\Requests;

use App\Helpers\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use MoonShine\UI\Fields\Enum;

class CodeSendPhoneFormRequest extends FormRequest
{
    public $type;

    public function rules(Request $request): array
    {

        $request->merge(['phone' => PhoneHelper::formatPhone($request->phone)]);

        $rule = "";
        if ($request->rule === 'exist') {
            $rule = "exists:users,phone";
        }
        if ($request->rule === 'dont_exist') {
            $rule = "unique:users,phone";
        }
        if ($request->rule === 'all') {
            $rule = "";
        }
        if ($request->rule === 'dont_exist_except_user') {
            $rule = "unique:users,phone,".auth()->user()->id;
        }

        return [
            'rule' => ['required', 'in:exist,dont_exist,all,dont_exist_except_user'],
            'target' => ['nullable'],
            'phone' => ['required', "regex:/^([0-9\s\-\+\(\)]*)$/", $rule],
        ];
    }

    public function messages()
    {
        return [
            'phone.unique' => 'Данный телефон уже зарегистрирован',
            'phone.required' => 'Телефон должен быть заполнен',
            'phone.exists' => 'Телефон не найден в базе данных',
        ];
    }

}
