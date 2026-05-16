<?php

namespace App\Modules\Localization\src\Http\Controllers;
use App\Http\Controllers\Controller;
use City\Models\City;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Localization\Models\Lang;

#[Group('Язык Пользователя', 'Сохранение языка пользователя в сессию')]
class UserLangController extends Controller
{

    #[BodyParam('lang_id', description: 'ID языка', required: true, example: 1)]
    #[Response(200, description: 'Язык успешно сохранён')]
    #[Response(404, description: 'Язык не найден')]
    public function setLang(Request $request)
    {
        $request->validate([
            'lang_id' => 'required|int',
        ]);

        $lang = Lang::find($request->lang_id);

        if (!$lang) {
            return response()->json(['message' => 'Язык не найден'], 404);
        }

        $sessionData = [
            'lang_id' => $lang->id,
            'lang' => $lang->title,
        ];

        session()->put('user_lang', $sessionData);

        return response()->json([
            'message' => 'Язык сохранён',
            'data' => $sessionData,
        ]);
    }
}
