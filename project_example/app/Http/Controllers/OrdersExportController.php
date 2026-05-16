<?php

namespace App\Http\Controllers;

use Order\Models\Order;
use Order\Models\OrderItem;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrdersExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $dateFrom = request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateTo = request()->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $orders = Order::whereBetween('created_at', [$periodStart, $periodEnd])
            ->with(['user', 'items.item', 'transactionMethod', 'orderPromocode'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'orders_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовок отчета
            fputcsv($file, ['ОТЧЕТ ПО ЗАКАЗАМ'], ';');
            fputcsv($file, ['Период: ' . $dateFrom . ' - ' . $dateTo], ';');
            fputcsv($file, ['Дата формирования: ' . now()->format('d.m.Y H:i:s')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Заголовки
            fputcsv($file, [
                'ID заказа',
                'Пользователь',
                'Email',
                'Телефон',
                'Статус',
                'Тип доставки',
                'Адрес доставки',
                'Сумма корзины',
                'Цена доставки',
                'Скидка',
                'Финальная цена',
                'Способ оплаты',
                'Промокод',
                'Количество товаров',
                'Дата создания'
            ], ';');

            // Данные
            foreach ($orders as $order) {
                $itemsCount = $order->items->sum('quantity');
                $userName = $order->user ? $order->user->name : 'Гость';
                $userEmail = $order->user ? $order->user->email : '';
                
                fputcsv($file, [
                    $order->id,
                    $userName,
                    $userEmail,
                    $order->phone ?? '',
                    $order->status?->toString() ?? '',
                    $order->delivery_type?->toString() === 'pickup' ? 'Самовывоз' : ($order->delivery_type?->toString() === 'courier' ? 'Курьер' : ''),
                    $order->delivery_address_for_admin ?? '',
                    number_format($order->cart_sum ?? 0, 2, '.', ''),
                    number_format($order->delivery_price ?? 0, 2, '.', ''),
                    number_format(($order->personal_discount ?? 0) + ($order->promocode_discount ?? 0), 2, '.', ''),
                    number_format($order->price_final ?? 0, 2, '.', ''),
                    $order->transactionMethod?->name ?? '',
                    $order->orderPromocode?->promocode ?? '',
                    $itemsCount,
                    $order->created_at->format('d.m.Y H:i:s')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

