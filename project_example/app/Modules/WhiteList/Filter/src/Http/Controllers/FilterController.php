<?php

namespace App\Modules\WhiteList\Filter\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Attribute\Models\ProductAttribute;
use Filter\Models\SavedFilter;
use Filter\Models\SavedFilterAttribute;
use Illuminate\Http\Request;
use List\Models\UserWhitelist;
use List\Models\WhitelistItem;

class FilterController extends Controller
{

//    #[BodyParam('price_from', 'integer', 'Минимальная цена', example: 1000, required: false)]
//    #[BodyParam('price_to', 'integer', 'Максимальная цена', example: 5000, required: false)]
//    #[BodyParam('brand_id', 'integer', 'ID бренда', example: 3, required: false)]
//    #[BodyParam('attributes', 'array', 'Список атрибутов со значениями', required: false)]
//    #[BodyParam('attributes[].attribute_id', 'integer', 'ID атрибута', example: 1)]
//    #[BodyParam('attributes[].value', 'string', 'Значение атрибута', example: "Вакуум")]
//    #[BodyParam('tags', 'array', 'Список ID тегов', required: false, example: [1, 2, 3])]
    public function saveFilter(Request $request)
    {
        $request->validate([
            'price_from' => 'nullable|integer',
            'price_to' => 'nullable|integer',
            'brand_id' => 'nullable|exists:brands,id',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required_with:attributes|exists:attributes,id',
            'attributes.*.value' => 'required_with:attributes|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $user = auth()->user();

        $filter = SavedFilter::create([
            'user_id' => 2,
            'price_from' => $request->price_from,
            'price_to' => $request->price_to,
            'brand_id' => $request->brand_id,
        ]);

        if ($request->filled('attributes')) {
            foreach ($request->input('attributes') as $attr) {
                $exists = ProductAttribute::where('attribute_id', $attr['attribute_id'])
                    ->where('value', $attr['value'])
                    ->exists();

                if ($exists) {
                    SavedFilterAttribute::create([
                        'saved_filter_id' => $filter->id,
                        'attribute_id' => $attr['attribute_id'],
                        'value' => $attr['value'],
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Значение аттрибута не найдено"
                    ], 404);
                }
            }
        }

        if ($request->filled('tags')) {
            $filter->tags()->attach($request->tags);
        }

        $whitelist = UserWhitelist::firstOrCreate(['user_id' => $user->id]);

        $alreadyExists = WhitelistItem::where('whitelist_id', $whitelist->id)
            ->where('item_type', SavedFilter::class)
            ->where('item_id', $filter->id)
            ->exists();

        if (!$alreadyExists) {
            WhitelistItem::create([
                'whitelist_id' => $whitelist->id,
                'item_id' => $filter->id,
                'item_type' => SavedFilter::class,
            ]);
        }


        return back()->with([
            'status' => 'success',
            'message' => 'Фильтр успешно сохранён и добавлен в избранное'
        ]);
    }


    public function deleteFilter($id)
    {
        $user = auth()->user();

        $filter = SavedFilter::where('id', $id)->where('user_id', $user->id)->first();

        if (!$filter) {

            return back()->with([
                'status' => 'error',
                'message' => 'Фильтр не найден'
            ]);
        }

        $whitelist = UserWhitelist::where('user_id', $user->id)->first();

        if ($whitelist) {
            WhitelistItem::where('whitelist_id', $whitelist->id)
                ->where('item_type', SavedFilter::class)
                ->where('item_id', $filter->id)
                ->delete();
        }

        $filter->delete();

        return back()->with([
            'status' => 'success',
            'message' => 'Фильтр удалён из избранного'
        ]);
    }



}
