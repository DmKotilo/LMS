<?php

namespace Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Order\Models\Order;

/**
 * API контроллер для интеграции с Битрикс24
 * Обрабатывает webhook'и от Битрикс24 для обновления deal_id
 */
class Bitrix24WebhookController extends Controller
{
    /**
     * Обновляет deal_id для заказа по lead_id
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function updateDealId(Request $request): JsonResponse
    {
        Log::info('Bitrix24 webhook received', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Валидация входящих данных
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|string',
            'deal_id' => 'required|string',
            'timestamp' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            Log::warning('Bitrix24 webhook validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $leadId = $request->input('lead_id');
        $dealId = $request->input('deal_id');

        try {
            // Ищем заказ по lead_id
            $order = Order::where('bitrix_lead_id', $leadId)->first();

            if (!$order) {
                Log::warning('Order not found by lead_id', [
                    'lead_id' => $leadId,
                    'deal_id' => $dealId
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                    'lead_id' => $leadId
                ], 404);
            }

            // Обновляем deal_id
            $order->update([
                'bitrix_deal_id' => $dealId
            ]);

            Log::info('Deal ID updated successfully', [
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'deal_id' => $dealId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deal ID updated successfully',
                'order_id' => $order->id,
                'lead_id' => $leadId,
                'deal_id' => $dealId
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update deal ID', [
                'lead_id' => $leadId,
                'deal_id' => $dealId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Получает информацию о заказе по lead_id
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderByLeadId(Request $request): JsonResponse
    {
        $leadId = $request->input('lead_id');

        if (!$leadId) {
            return response()->json([
                'success' => false,
                'message' => 'lead_id is required'
            ], 400);
        }

        $order = Order::where('bitrix_lead_id', $leadId)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'lead_id' => $leadId
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => [
                'id' => $order->id,
                'name' => $order->name,
                'last_name' => $order->last_name,
                'phone' => $order->phone,
                'email' => $order->email,
                'price_final' => $order->price_final,
                'status' => $order->status?->value,
                'bitrix_lead_id' => $order->bitrix_lead_id,
                'bitrix_deal_id' => $order->bitrix_deal_id,
                'metrica_client_id' => $order->metrica_client_id,
                'created_at' => $order->created_at,
            ]
        ]);
    }

    /**
     * Проверка статуса интеграции
     * 
     * @return JsonResponse
     */
    public function status(): JsonResponse
    {
        $ordersWithLeads = Order::whereNotNull('bitrix_lead_id')->count();
        $ordersWithDeals = Order::whereNotNull('bitrix_deal_id')->count();
        $ordersWithoutDeals = Order::whereNotNull('bitrix_lead_id')
            ->whereNull('bitrix_deal_id')
            ->count();

        return response()->json([
            'success' => true,
            'integration_status' => [
                'total_orders_with_leads' => $ordersWithLeads,
                'total_orders_with_deals' => $ordersWithDeals,
                'orders_waiting_for_deals' => $ordersWithoutDeals,
                'integration_enabled' => config('services.bitrix24.enabled', true),
            ]
        ]);
    }
}
