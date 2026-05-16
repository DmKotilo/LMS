<?php

namespace Api\Http\Controllers\v1;


use Api\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Modules\City\src\Models\CityProductCount;
use App\MoonShine\Resources\Product\CityCounts\CityCountResource;
use Brand\Models\Brand;
use Category\Models\Category;
use City\Models\City;
use File\Models\Files\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseField;
use Product\Models\Product;
use function Symfony\Component\String\s;

#[Group('1С')]
class ProductController extends BaseController
{
    #[Endpoint('Обмен товарами', 'Обрабатывает массив товаров для создания/обновления')]
    #[Authenticated]
    #[BodyParam('products', 'array','Массив товаров',required: true)]
    #[BodyParam('products.uuid','string','Уникальный идентификатор товара 1с',example:'uuid-123',required: true)]
    #[BodyParam('products.uuid_bitrix24','string','Уникальный идентификатор товара БИТРИКС24 ',example:'12345',required: true)]
    #[BodyParam('products.sku','string','Артикул', example:'343414',required: true)]
    #[BodyParam('products.name', 'string', 'Название товара',example: 'Товар 1',required: true)]
    #[BodyParam('products.weight','number','Вес товара', example: 1.5,required: false)]
  /*  #[BodyParam('products.price','number','Цена товара', example: 1569,required: true)]*/
    #[BodyParam('products.description','string','Описание товара', example:'Описание товара',required: false)]
    #[BodyParam('products.category','string','Категория товара', example:'Категория 1',required: false)]
    #[BodyParam('products.subcategory','string','Подкатегория товара', example:'Подкатегория 4',required: false)]
   /* #[BodyParam('products.images', 'array', 'Массив изображений в base64 формате',example:['base64string1', 'base64string2'],required: false)]
    #[BodyParam( 'products.images.*', 'string','Изображение в формате base64',required: false)]*/
    #[BodyParam( 'products.is_active','boolean', 'Активность товара',required: true)]
    #[BodyParam( 'products.is_analog','boolean', 'Является ли товар аналогом',required: true)]
    #[BodyParam( 'products.is_weight_show','boolean', 'Показывать ли вес товара',required: false)]
    #[ResponseField( 'status', 'string', 'Статус ответа', 'ok',)]
    #[ResponseField( 'data', 'array', 'Список товаров после обработки',)]
    public function exchange(Request $request)
    {
        $data = $request->validate([
            "products" => ['required', 'array'],
            "products.*.uuid" => ['nullable'],
            "products.*.uuid_bitrix24" => ['nullable'],
            "products.*.name" => ['nullable'],
            "products.*.brand" => ['nullable'],
            "products.*.weight" => ['nullable'],
            "products.*.description" => ['nullable'],
            "products.*.category" => ['nullable'],
            "products.*.subcategory" => ['nullable'],
            "products.*.sku" => ['nullable'],
            "products.*.is_active" => ['nullable'],
            "products.*.is_analog" => ['nullable'],
            "products.*.is_weight_show" => ['nullable', 'boolean'],
        ]);

        $response = [];

        foreach ($data['products'] as $item) {

            $product = null;

            try {

                $validator = Validator::make($item, [
                    "uuid" => ['required', 'string'],
                    "uuid_bitrix24" => ['required', 'string'],
                    "name" => ['required', 'string', 'max:255'],
                    "brand" => ['nullable', 'string','exists:brands,uuid_1c'],
                    "weight" => ['nullable'],
                    "description" => ['nullable', 'string'],
                    "category" => ['nullable', 'string'],
                    "subcategory" => ['nullable', 'string'],
                    "sku" => ['required', 'string'],
                    "is_active" => ['required', 'boolean'],
                    "is_analog" => ['required', 'boolean'],
                    "is_weight_show" => ['nullable', 'boolean'],
                ])->validated();

                $product = Product::query()->where("uuid_bitrix24", $item['uuid_bitrix24'])->first();
                /*if(!$product)
                $product = Product::query()->where("uuid_1c", $item['uuid'])->first();*/

                if ($product) {
                    $product->update([
                        "uuid_1c" => $item['uuid'],
                        "uuid_bitrix24" => $item['uuid_bitrix24'],
                        "name_1c" => $item['name'],
                        "is_active" => $item['is_active'],
                        "is_analog" => $item['is_analog'],
                        "description_1c" => $item['description'] ?? null,
                        "category_1c" => $item['category'] ?? null,
                        "subcategory_1c" => $item['subcategory'] ?? null,
                        "weight" => $item['weight'] ?? null,
                        "article_number" => $item['sku'] ?? null,
                        "is_weight_show" => $item['is_weight_show'] ?? null,
                    ]);
                    Log::info('1C exchange: товар обновлён', [
                        'uuid_bitrix24' => $product->uuid_bitrix24,
                        'product_id' => $product->id,
                    ]);
                } else {
                    $product = Product::query()->create([
                        "uuid_1c" => $item['uuid'],
                        "uuid_bitrix24" => $item['uuid_bitrix24'],
                        "name" => $item['name'],
                        "name_1c" => $item['name'],
                        "is_active" => $item['is_active'],
                        "is_analog" => $item['is_analog'],
                        "description_1c" => $item['description'] ?? null,
                        "category_1c" => $item['category'] ?? null,
                        "subcategory_1c" => $item['subcategory'] ?? null,
                        "weight" => $item['weight'] ?? null,
                        "article_number" => $item['sku'] ?? null,
                        "is_weight_show" => $item['is_weight_show'] ?? null,
                        "slug" => Str::slug($item['name']),
                    ]);
                    Log::info('1C exchange: товар создан', [
                        'uuid_bitrix24' => $product->uuid_bitrix24,
                        'product_id' => $product->id,
                    ]);
                }

                   /* if (!empty($item['images'])) {

                        foreach ($item['images'] as $index => $image) {
                            if (strpos($image, ',') !== false) {
                                [, $image] = explode(',', $image, 2);
                            }
                            $this->saveProductImage($product, $image, $index);
                        }
                    }*/

                if(!empty($item['brand'])) {
                    $brand = Brand::where('uuid_1c', $item['brand'])->first();
                    $product->brands()->sync([$brand->id]);
                }

                $response[] = [
                    'uuid_bitrix24' => $product->uuid_bitrix24,
                    'site_id' => $product?->id,
                    'status'=> 'success',
                ];

            } catch (\Throwable $exception) {
                $response[] = [
                    'uuid_bitrix24' => $item['uuid_bitrix24'],
                    'site_id' => $product?->id,
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
            }
        }

        return $this->sendResponse("success", $response);
    }

    #[Endpoint('Установить количество товара по городам', 'Обрабатывает массив товаров для создания/обновления')]
    #[Authenticated]
    #[BodyParam('products', 'array','Массив товаров',required: true)]
    #[BodyParam('products.uuid_bitrix24','string','Уникальный идентификатор товара БИТРИКС24',example:'123456',required: true)]
    #[BodyParam('products.uuid_city','string','Уникальный идентификатор города',example:'uuid-123-1212',required: true)]
    #[BodyParam('products.quantity','number','Количество товара', example: 1.5,required: false)]
    #[ResponseField('status', 'string', 'Статус ответа', 'ok',)]
    #[ResponseField('data', 'array', 'Список после обработки',)]
    public function quantity_by_city(Request $request)
    {
        $data = $request->validate([
            "products" => ['required', 'array'],
            "products.*.uuid_bitrix24" => ['nullable'],
            "products.*.uuid_city" => ['nullable'],
            "products.*.quantity" => ['nullable'],
        ]);

        $response = [];

        foreach ($data['products'] as $item) {
            try {

                $validator = Validator::make($item, [
                    "uuid_bitrix24" => ['required', 'string', 'exists:products,uuid_bitrix24'],
                    "uuid_city" => ['required', 'string', 'exists:cities,uuid_1c'],
                    "quantity" => ['required', 'integer'],
                ])->validated();

                $product = Product::query()->where("uuid_bitrix24", $item['uuid_bitrix24'])->first();
                $city = City::query()->where("uuid_1c", $item['uuid_city'])->first();
                CityProductCount::updateOrCreate([
                    'city_id' =>  $city->id,
                    'product_id' =>  $product->id
                ], [
                    'quantity' =>  $item['quantity']
                ]);

                $response[] = [
                    'uuid_bitrix24' => $product->uuid_bitrix24,
                    'uuid_city' => $city->uuid_1c,
                    'status'=> 'success',
                ];

            } catch (\Throwable $exception) {
                $response[] = [
                    'uuid_bitrix24' => $item['uuid_bitrix24'] ?? 'не передан',
                    'uuid_city' => $item['uuid_city'] ?? 'не передан',
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
            }
        }

        return $this->sendResponse("success", $response);
    }

    #[Endpoint('Установить цену товара', 'Обрабатывает массив товаров')]
    #[Authenticated]
    #[BodyParam('products', 'array','Массив товаров',required: true)]
    #[BodyParam('products.uuid_bitrix24','string','Уникальный идентификатор товара БИТРИКС24',example:'123456',required: true)]
    #[BodyParam('products.price','float','Цена товара', example: 1.5,required: false)]
    #[ResponseField('status', 'string', 'Статус ответа', 'ok',)]
    #[ResponseField('data', 'array', 'Список после обработки',)]
    public function prices(Request $request)
    {

/*        ini_set('max_execution_time', 600);*/

        $data = $request->validate([
            "products" => ['required', 'array'],
            "products.*.uuid_bitrix24" => ['nullable'],
            "products.*.price" => ['nullable'],
        ]);

        $response = [];

        foreach ($data['products'] as $item) {
            try {

                $validator = Validator::make($item, [
                    "uuid_bitrix24" => ['required', 'string', 'exists:products,uuid_bitrix24'],
                    "price" => ['required', 'numeric'],
                ])->validated();

                $product = Product::query()->where("uuid_bitrix24", $item['uuid_bitrix24'])->first();
                $product->update(['price' => $validator['price']]);

                Log::info('1C prices: цена обновлена', [
                    'uuid_bitrix24' => $product->uuid_bitrix24,
                    'product_id' => $product->id,
                    'price' => $validator['price'],
                ]);

                $response[] = [
                    'uuid_bitrix24' => $product->uuid_bitrix24,
                    'status'=> 'success',
                ];

            } catch (\Throwable $exception) {
                $response[] = [
                    'uuid_bitrix24' => $item['uuid_bitrix24'],
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
            }
        }

        return $this->sendResponse("success", $response);
    }


    private function saveProductImage(Product $product, string $base64Image, int $index = 0)
    {
        $imageData = base64_decode($base64Image);
        $extension = 'png'; // hardCode
        $fileName = uniqid() . '.' . $extension;
        $storagePath = "Product/{$product->id}/image/{$fileName}";
        Storage::disk('public')->put($storagePath, $imageData);
        File::create([
            'fileable_id' => $product->id,
            'fileable_type' => Product::class,
            'file_name' => $fileName,
            'path' => $storagePath,
            'type_relation' => 'images',
            'extension' => $extension,
            'size' => strlen($imageData),
            'disk' => 'public',
            'position' => $index,
        ]);
    }

}
