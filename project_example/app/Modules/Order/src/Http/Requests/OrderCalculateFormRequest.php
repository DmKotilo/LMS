<?php

namespace Order\Http\Requests;

use Address\Model\ContactMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Order\Enums\OrderDeliveryTypeEnum;

class OrderCalculateFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'product_id' => ['nullable', 'exists:products,id'], //передается при кнопке "Купить сейчас", быстрая покупка. Если передается, то вся корзина не очищается после заказа
            'combo_id' => ['nullable', 'exists:combos,id'], //передается при кнопке "Купить сейчас", быстрая покупка. Если передается, то вся корзина не очищается после заказа
            'delivery_type' => ['nullable', Rule::enum(OrderDeliveryTypeEnum::class)],
            'promo' => ['nullable', 'string'],
            'use_expiring_bonuses' => ['nullable', ''],
            'pickup_location_id' => ['nullable','exists:pickup_locations,id'],
            'delivery_zone_id' => ['nullable', 'exists:delivery_zones,id'],
            'address_id' => ['nullable','exists:addresses,id'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_time_from' => ['nullable', 'string', 'required_with:delivery_time_to'],
            'delivery_time_to' => ['nullable', 'string', 'required_with:delivery_time_from'],
            'city_id' => ['nullable', 'integer'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
        ];
    }

}
