<?php

namespace App\Modules\City\src\Http\Controllers;

use App\Http\Controllers\Controller;
use City\Models\City;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group('Город Пользователя', 'Сохранение города пользователя в сессию')]
class UserCityController extends Controller
{
    #[BodyParam('city_id', description: 'ID города', required: true, example: 1)]
    #[Response(200, description: 'Город успешно сохранён')]
    #[Response(404, description: 'Город не найден')]
    public function setCity(Request $request)
    {
        $request->validate([
            'city_id' => 'required|int',
        ]);

        $city = City::find($request->city_id);

        if (!$city) {
            return response()->json(['message' => 'Город не найден'], 404);
        }

        $sessionData = [
            'city_id' => $city->id,
            'city' => $city->name,
            'latitude' => $city->latitude,
            'longitude' => $city->longitude,
        ];

        session()->put('user_city', $sessionData);

        return response()->json([
            'success' => 'Город сохранён',
            'data' => $sessionData,
        ]);
    }
}
