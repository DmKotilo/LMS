<?php

namespace Order\Http\Requests;

use Address\Model\ContactMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Order\Enums\OrderDeliveryTypeEnum;

class OrderStoreFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'product_id' => ['nullable', 'exists:products,id'], //передается при кнопке "Купить сейчас", быстрая покупка. Если передается, то вся корзина не очищается после заказа
            'combo_id' => ['nullable', 'exists:combos,id'], //передается при кнопке "Купить сейчас", быстрая покупка. Если передается, то вся корзина не очищается после заказа
            'transaction_method_id' => ['required', 'exists:transaction_methods,id'],
            'delivery_type' => ['required', Rule::enum(OrderDeliveryTypeEnum::class)],
            'pickup_location_id' => ['sometimes', 'required_if:delivery_type,'.OrderDeliveryTypeEnum::pickup->value,'exists:pickup_locations,id'],
            'address_id' => ['sometimes', 'required_if:delivery_type,'.OrderDeliveryTypeEnum::courier->value,'exists:addresses,id'],
            'contact_methods' => ['nullable', 'array'],
            'contact_methods.*' => ['required', 'exists:contact_methods,id'],
            'name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'second_name' => ['nullable', 'string'], //отчество
            'phone' => ['required', 'string'],
            'email' => ['required', 'email'],
            'comment' => ['nullable', 'string'],
            'promo' => ['nullable', 'string'],
            'use_expiring_bonuses' => ['nullable', 'boolean'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_time_from' => ['nullable', 'string', 'required_with:delivery_time_to'],
            'delivery_time_to' => ['nullable', 'string', 'required_with:delivery_time_from'],
            'metrica_client_id' => ['nullable', 'string'],
        ];
    }

}
