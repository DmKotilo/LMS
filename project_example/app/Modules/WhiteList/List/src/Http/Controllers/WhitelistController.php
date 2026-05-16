<?php

namespace App\Modules\WhiteList\List\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\WhiteList\List\src\Enums\WhitelistItemTypeEnum;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use List\Models\UserWhitelist;
use List\Models\WhitelistItem;
use Illuminate\Http\Request;
#[Group('Избранное', 'Работа с избранным')]
class WhitelistController extends Controller
{
    #[Endpoint('Добавить элемент в избранное', 'Добавляет элемент (item_id и item_type) в избранный список пользователя')]
    #[BodyParam('item_id', description: 'ID элемента', required: true, example: 123)]
    #[BodyParam(
        'item_type',
        description: 'Тип элемента. Возможные значения: "product", "recipe"',
        required: true,
        example: 'recipe'
    )]

    #[Response(
        content: '{"status":"success","message":"Добавлено в избранное"}',
        description: 'Успешное добавление',
        status: 200
    )]
    #[Response(
        content: '{"status":"warning","message":"Элемент уже в избранном"}',
        description: 'Элемент уже добавлен',
        status: 400
    )]
    public function add(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->to(
                url()->previous() . (str_contains(url()->previous(), '?') ? '&' : '?') . 'modal=login'
            );
        }


        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => ['required',  Rule::in(['product', 'recipe'])],
        ]);

        $user = auth()->user();

        $whitelist = UserWhitelist::firstOrCreate(['user_id' => $user->id]);
        $itemType = match ($request->item_type) {
            'product' => WhitelistItemTypeEnum::Product->value,
            'recipe' => WhitelistItemTypeEnum::Recipe->value,
        };
        $exists = WhitelistItem::where('whitelist_id', $whitelist->id)
            ->where('item_id', $request->item_id)
            ->where('item_type', $itemType)
            ->exists();

        if ($exists) {
            return back()->with([
                'status' => 'warning',
                'message' => 'Элемент уже в избранном'
            ]);
        }

        WhitelistItem::create([
            'whitelist_id' => $whitelist->id,
            'item_id' => $request->item_id,
            'item_type' => $itemType,
        ]);

        return redirect()->back()->with([
            'status' => 'success',
            'success' => 'Добавлено в избранное'
        ]);
    }


    #[Endpoint('Удалить элемент из избранного', 'Удаляет элемент (item_id и item_type) из избранного списка пользователя')]
    #[BodyParam('item_id', description: 'ID элемента', required: true, example: 123)]
    #[BodyParam(
        'item_type',
        description: 'Тип элемента. Возможные значения: "product", "recipe"',
        required: true,
        example: 'product'
    )]

    #[Response(
        content: '{"status":"success","message":"Удалено из избранного"}',
        description: 'Успешное удаление',
        status: 200
    )]
    #[Response(
        content: '{"status":"error","message":"Избранный список не найден"}',
        description: 'Избранный список отсутствует',
        status: 404
    )]
    #[Response(
        content: '{"status":"error","message":"Элемент не найден в избранном"}',
        description: 'Элемент не найден в избранном',
        status: 404
    )]
    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'item_type' => ['required',  Rule::in(['product', 'recipe'])],

        ]);

        $user = auth()->user();

        $whitelist = UserWhitelist::where('user_id', $user->id)->first();

        if (!$whitelist) {
            return back()->with([
                'status' => 'error',
                'error' => 'Избранный список не найден'
            ]);
        }
        $itemType = match ($request->item_type) {
            'product' => WhitelistItemTypeEnum::Product->value,
            'recipe' => WhitelistItemTypeEnum::Recipe->value,
        };
        $item = WhitelistItem::where('whitelist_id', $whitelist->id)
            ->where('item_id', $request->item_id)
            ->where('item_type', $itemType)
            ->first();

        if (!$item) {
            return back()->with([
                'status' => 'error',
                'error' => 'Элемент не найден в избранном'
            ]);
        }

        $item->delete();

        return back()->with([
            'status' => 'success',
            'success' => 'Удалено из избранного'
        ]);
    }
}
