<?php

namespace App\Modules\Cart\src\Traits;

use App\Modules\Cart\src\Enums\CartStatusEnum;
use Cart\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait GetCart
{
    protected function getCart(Request $request): UserCart
    {
        $sessionId = $request->session()->getId();

        if ($request->user()) {
            // Сначала ищем гостевую корзину по текущей сессии
            $guestCart = UserCart::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->where('status', CartStatusEnum::Active)
                ->first();

            // Ищем корзину пользователя по user_id
            $userCart = UserCart::where('user_id', $request->user()->id)
                ->where('status', CartStatusEnum::Active)
                ->first();

            // Если есть и гостевая корзина, и корзина пользователя - нужно объединить
            if ($guestCart && $userCart && $guestCart->id !== $userCart->id) {
                Log::info('Cart merge: Found both guest and user carts', [
                    'guest_cart_id' => $guestCart->id,
                    'user_cart_id' => $userCart->id,
                    'user_id' => $request->user()->id,
                    'session_id' => $sessionId,
                ]);

                // Загружаем товары обеих корзин
                $guestCart->load('items');
                $userCart->load('items');

                // Объединяем товары из гостевой корзины в корзину пользователя
                foreach ($guestCart->items as $guestItem) {
                    $existingItem = $userCart->items()
                        ->where('item_type', $guestItem->item_type)
                        ->where('item_id', $guestItem->item_id)
                        ->where('is_gift', $guestItem->is_gift)
                        ->first();

                    if ($existingItem) {
                        // Если товар уже есть - увеличиваем количество
                        $existingItem->quantity += $guestItem->quantity;
                        $existingItem->save();
                        
                        Log::info('Cart merge: Merged item quantity', [
                            'item_type' => $guestItem->item_type,
                            'item_id' => $guestItem->item_id,
                            'guest_quantity' => $guestItem->quantity,
                            'new_total_quantity' => $existingItem->quantity,
                        ]);
                    } else {
                        // Если товара нет - переносим его
                        $guestItem->update(['cart_id' => $userCart->id]);
                        
                        Log::info('Cart merge: Moved item to user cart', [
                            'item_type' => $guestItem->item_type,
                            'item_id' => $guestItem->item_id,
                            'quantity' => $guestItem->quantity,
                        ]);
                    }
                }

                // Удаляем гостевую корзину после объединения
                $guestCart->delete();

                // Обновляем session_id корзины пользователя
                $userCart->update(['session_id' => $sessionId]);
                
                Log::info('Cart merge: Completed', [
                    'user_cart_id' => $userCart->id,
                    'items_count' => $userCart->items()->count(),
                ]);
                
                return $userCart;
            }

            // Если есть только гостевая корзина - привязываем её к пользователю
            if ($guestCart) {
                $guestCart->update(['user_id' => $request->user()->id]);
                return $guestCart;
            }

            // Если есть только корзина пользователя - обновляем session_id и возвращаем
            if ($userCart) {
                // Обновляем session_id только если он отличается
                if ($userCart->session_id !== $sessionId) {
                    $oldSessionId = $userCart->session_id;
                    $userCart->update(['session_id' => $sessionId]);
                    
                    Log::info('Cart sync: Updated session_id for user cart', [
                        'user_cart_id' => $userCart->id,
                        'old_session_id' => $oldSessionId,
                        'new_session_id' => $sessionId,
                        'user_id' => $request->user()->id,
                    ]);
                }
                return $userCart;
            }

            // Если корзин нет - создаём новую
            return UserCart::create([
                'user_id' => $request->user()->id,
                'session_id' => $sessionId,
                'status' => CartStatusEnum::Active,
            ]);
        }

        // Для неавторизованных пользователей - просто ищем/создаём по session_id
        return UserCart::firstOrCreate(
            ['user_id' => null, 'session_id' => $sessionId, 'status' => CartStatusEnum::Active]
        );
    }

}
