<?php

namespace Api\Http\Controllers\v1;


use Api\Http\Controllers\BaseController;
use City\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseField;
#[Group('1С')]
class CityController extends BaseController
{
    #[Endpoint('Обмен городами', 'Обрабатывает массив городов для создания/обновления')]
    #[Authenticated]
    #[BodyParam('cities', 'array', 'Массив городов', required: true)]
    #[BodyParam('cities.name', 'string', 'Название бренда', required: true, example: 'Москва')]
    #[BodyParam('cities.uuid', 'string', 'Уникальный идентификатор', required: true, example: 'uuid-city-123')]
    #[BodyParam('cities.is_active', 'boolean', '', required: true, example: 'uuid-city-123')]
    #[ResponseField('status', 'string', 'Статус ответа', 'ok')]
    #[ResponseField('data', 'array', 'Список городов после обработки')]
    public function exchange(Request $request)
    {
        $data = $request->validate([
            "cities" => ['required','array'],
            "cities.*.name" => ['nullable'],
            "cities.*.uuid" => ['nullable'],
            "cities.*.is_active" => ['nullable'],
        ]);

        $response = [];

        foreach ($data['cities'] as $key => $item){
            try {

                $validator = Validator::make($item, [
                    "name" => ['required','string','max:255'],
                    "uuid" => ['required','string'],
                    "is_active" => ['required','boolean'],
                ])->validated();
                //TODO добавить к городам is_active
                $city = City::updateOrCreate(["uuid_1c" => $item['uuid']], [
                    "name" => $item['name'],
                ]);
                $response[] = [
                    'uuid' => $city->uuid_1c,
                    'status'=> 'success',
                ];
            } catch (\Throwable $exception) {

                $response[] = [
                    'uuid' => $item['uuid'] ?? 'неизвестный uuid - '.$key,
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
            }
        }
        return $this->sendResponse("success", $response);
    }
}
