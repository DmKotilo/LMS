<?php

namespace Api\Http\Controllers\v1;


use Api\Http\Controllers\BaseController;
use Category\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseField;
#[Group('1С')]
class CategoryController extends BaseController
{
   /* #[Endpoint('Обмен категориями', 'Обрабатывает массив категорий для создания/обновления')]
    #[Authenticated]
    #[BodyParam('categories', 'array', 'Массив категорий', required: true)]
    #[BodyParam('categories.name', 'string', 'Название категории', required: true, example: 'Категория 1')]
    #[BodyParam('categories.uuid', 'string', 'Уникальный идентификатор категории', required: true, example: 'uuid-cat-123')]
    #[BodyParam('categories.parent_uuid', 'string', 'UUID родительской категории', required: false, example: 'uuid-parent-456')]
    #[ResponseField('status', 'string', 'Статус ответа', 'ok')]
    #[ResponseField('data', 'array', 'Список категорий после обработки')]
    public function exchange(Request $request)
    {
        $data = $request->validate([
            "categories" => ['required','array'],
            "categories.*.name" => ['required','string','max:255'],
            "categories.*.uuid" => ['required','string'],
            "categories.*.parent_uuid" => ['nullable','string'],
        ]);
        foreach ($data['categories'] as $item){
            $category = Category::query()->where("uuid_1c",$item['uuid'])->first();
            if ($category){
                $category->update([
                    "uuid_1c" => $item['uuid'],
                    "name_1c" => $item['name'],
                ]);
            }else{
                $category = Category::query()->create([
                    "uuid_1c" => $item['uuid'],
                    "name_1c" => $item['name'],
                    "name" => $item['name'],
                ]);
                $category->update(["slug" => Str::slug($category->name."-".$category->id)]);

            }
        }

        $categoriesWithParent = array_filter($data['categories'], function ($item) {
            return !empty($item['parent_uuid']);
        });
        foreach ($categoriesWithParent as $parent){
            $category = Category::query()->where("uuid_1c",$parent['uuid'])->first();
            $parent_category = Category::query()->where("uuid_1c",$parent['parent_uuid'])->first();
            if ($parent_category){
                $category->update(["parent_category_id" => $parent_category->id]);
            }
        }
        return $this->sendResponse("ok",Category::query()->orderByDesc("id")->get());
    }*/
}
