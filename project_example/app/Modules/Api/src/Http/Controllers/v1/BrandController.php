<?php

namespace Api\Http\Controllers\v1;


use Api\Http\Controllers\BaseController;
use Brand\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseField;
#[Group('1С')]
class BrandController extends BaseController
{
    #[Endpoint('Обмен брендами', 'Обрабатывает массив брендов для создания/обновления')]
    #[Authenticated]
    #[BodyParam('brands', 'array', 'Массив брендов', required: true)]
    #[BodyParam('brands.name', 'string', 'Название бренда', required: true, example: 'Бренд 1')]
    #[BodyParam('brands.uuid', 'string', 'Уникальный идентификатор бренда', required: true, example: 'uuid-brand-123')]
    #[BodyParam('brands.is_active', 'string', 'Активность в 1С', required: true, example: '1 | 0')]
    #[ResponseField('status', 'string', 'Статус ответа', 'ok')]
    #[ResponseField('data', 'array', 'Список брендов после обработки')]
    public function exchange(Request $request)
    {
        $data = $request->validate([
            "brands" => ['required','array'],
            "brands.*.name" => ['nullable'],
            "brands.*.uuid" => ['nullable'],
            "brands.*.is_active" => ['nullable'],
        ]);

        $response = [];

        foreach ($data['brands'] as $item){
            try {

                $validator = Validator::make($item, [
                    "name" => ['required','string','max:255'],
                    "uuid" => ['required','string'],
                    "is_active" => ['required','boolean'],
                ])->validated();

                $brand = Brand::query()->where("uuid_1c", $item['uuid'])->first();
                $brand = Brand::updateOrCreate(["uuid_1c" => $item['uuid']], [
                    "name_1c" => $item['name'],
                    "name" => $brand->name ?? $item['name'],
                    "is_active" => $item['is_active'],
                    'slug' => Str::slug($brand->name ?? $item['name']),
                ]);
                $response[] = [
                    'uuid' => $brand->uuid_1c,
                    'status'=> 'success',
                ];
            } catch (\Throwable $exception) {
                $response[] = [
                    'uuid' => $item['uuid'],
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
            }


        }
        return $this->sendResponse("success", $response);
    }
}
