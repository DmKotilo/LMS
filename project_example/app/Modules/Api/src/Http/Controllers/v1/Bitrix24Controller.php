<?php

namespace Api\Http\Controllers\v1;

use Api\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Order\Models\Order;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\Authenticated;

class Bitrix24Controller extends BaseController
{
    #[Group('Bitrix24')]
    #[Endpoint('Обновление deal_id для заказа', 'Обновляет ID сделки в Битрикс24 для заказа по lead_id')]
    #[Authenticated]
    #[BodyParam('lead_id', 'string', 'ID лида из Битрикс24', required: true, example: '123')]
    #[BodyParam('deal_id', 'string', 'ID сделки из Битрикс24', required: true, example: '456')]
    #[BodyParam('timestamp', 'integer', 'Временная метка запроса', required: false, example: 1704297600)]
    #[ResponseField('order_id', 'integer', 'ID заказа в системе', required: true)]
    #[ResponseField('lead_id', 'string', 'ID лида', required: true)]
    #[ResponseField('deal_id', 'string', 'ID сделки', required: true)]
    #[Response([
        'success' => true,
        'message' => 'Deal ID updated successfully',
        'data' => [
            'order_id' => 42,
            'lead_id' => '123',
            'deal_id' => '456'
        ]
    ])]
    #[Response([
        'success' => false,
        'error' => 'Order not found',
        'data' => ['lead_id' => '123']
    ], 404)]
    #[Response([
        'success' => false,
        'error' => 'Validation failed',
        'data' => ['errors' => ['lead_id' => ['The lead id field is required.']]]
    ], 422)]
    public function updateDealId(Request $request)
    {
        Log::channel('bitrix24')->info('Bitrix24 API: updateDealId called', [
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all()
        ]);

        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|string',
            'deal_id' => 'required|string',
            'timestamp' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            Log::channel('bitrix24')->warning('Bitrix24 API: validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);

            return $this->sendError('Validation failed', $validator->errors(), 422);
        }

        $leadId = $request->input('lead_id');
        $dealId = $request->input('deal_id');

        try {
            // Ищем заказ по lead_id
            $order = Order::where('bitrix_lead_id', $leadId)->first();

            if (!$order) {
                Log::channel('bitrix24')->warning('Bitrix24 API: order not found', [
                    'lead_id' => $leadId,
                    'deal_id' => $dealId
                ]);

                return $this->sendError('Order not found', ['lead_id' => $leadId], 404);
            }

            // Обновляем deal_id
            $order->update([
                'bitrix_deal_id' => $dealId
            ]);

            Log::channel('bitrix24')->info('Bitrix24 API: deal ID updated successfully', [
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'deal_id' => $dealId
            ]);

            return $this->sendResponse('Deal ID updated successfully', [
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'deal_id' => $dealId
            ]);

        } catch (\Exception $e) {
            Log::channel('bitrix24')->error('Bitrix24 API: update deal ID failed', [
                'lead_id' => $leadId,
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Internal server error', ['error' => $e->getMessage()], 500);
        }
    }

    #[Group('Bitrix24')]
    #[Endpoint('Получение заказа по lead_id', 'Получает информацию о заказе по ID лида из Битрикс24')]
    #[Authenticated]
    #[QueryParam('lead_id', 'string', 'ID лида из Битрикс24', required: true, example: '123')]
    #[ResponseField('id', 'integer', 'ID заказа', required: true)]
    #[ResponseField('name', 'string', 'Имя получателя', required: true)]
    #[ResponseField('last_name', 'string', 'Фамилия получателя', required: true)]
    #[ResponseField('phone', 'string', 'Телефон получателя', required: true)]
    #[ResponseField('email', 'string', 'Email получателя', required: true)]
    #[ResponseField('price_final', 'float', 'Итоговая цена заказа', required: true)]
    #[ResponseField('status', 'string', 'Статус заказа', required: true)]
    #[ResponseField('bitrix_lead_id', 'string', 'ID лида в Битрикс24', required: false)]
    #[ResponseField('bitrix_deal_id', 'string', 'ID сделки в Битрикс24', required: false)]
    #[ResponseField('created_at', 'string', 'Дата создания заказа', required: true)]
    #[Response([
        'success' => true,
        'message' => 'Order found',
        'data' => [
            'id' => 42,
            'name' => 'Иван',
            'last_name' => 'Иванов',
            'phone' => '+79991234567',
            'email' => 'ivan@example.com',
            'price_final' => 5500.00,
            'status' => 'pending',
            'bitrix_lead_id' => '123',
            'bitrix_deal_id' => '456',
            'created_at' => '2024-01-15 12:30:45'
        ]
    ])]
    #[Response([
        'success' => false,
        'error' => 'Order not found',
        'data' => ['lead_id' => '123']
    ], 404)]
    public function getOrderByLeadId(Request $request)
    {
        $leadId = $request->input('lead_id');

        if (!$leadId) {
            return $this->sendError('lead_id is required', null, 400);
        }

        $order = Order::where('bitrix_lead_id', $leadId)->first();

        if (!$order) {
            return $this->sendError('Order not found', ['lead_id' => $leadId], 404);
        }

        return $this->sendResponse('Order found', [
            'id' => $order->id,
            'name' => $order->name,
            'last_name' => $order->last_name,
            'phone' => $order->phone,
            'email' => $order->email,
            'price_final' => $order->price_final,
            'status' => $order->status?->value,
            'bitrix_lead_id' => $order->bitrix_lead_id,
            'bitrix_deal_id' => $order->bitrix_deal_id,
            'created_at' => $order->created_at,
        ]);
    }

    #[Group('Bitrix24')]
    #[Endpoint('Статус интеграции', 'Проверка статуса интеграции с Битрикс24')]
    #[Authenticated]
    #[ResponseField('total_orders_with_leads', 'integer', 'Всего заказов с лидами', required: true, example: 100)]
    #[ResponseField('total_orders_with_deals', 'integer', 'Всего заказов со сделками', required: true, example: 95)]
    #[ResponseField('orders_waiting_for_deals', 'integer', 'Заказов ожидающих присвоения сделки', required: true, example: 5)]
    #[ResponseField('integration_enabled', 'boolean', 'Интеграция включена', required: true)]
    #[Response([
        'success' => true,
        'message' => 'Integration status',
        'data' => [
            'total_orders_with_leads' => 100,
            'total_orders_with_deals' => 95,
            'orders_waiting_for_deals' => 5,
            'integration_enabled' => true
        ]
    ])]
    public function status()
    {
        $ordersWithLeads = Order::whereNotNull('bitrix_lead_id')->count();
        $ordersWithDeals = Order::whereNotNull('bitrix_deal_id')->count();
        $ordersWithoutDeals = Order::whereNotNull('bitrix_lead_id')
            ->whereNull('bitrix_deal_id')
            ->count();

        return $this->sendResponse('Integration status', [
            'total_orders_with_leads' => $ordersWithLeads,
            'total_orders_with_deals' => $ordersWithDeals,
            'orders_waiting_for_deals' => $ordersWithoutDeals,
            'integration_enabled' => config('services.bitrix24.enabled', true),
        ]);
    }
}

