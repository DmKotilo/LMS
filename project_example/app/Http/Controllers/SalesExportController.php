<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Order\Models\Order;
use Order\Models\OrderItem;
use Product\Models\Product;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $dateFrom = request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateTo = request()->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $filename = 'sales_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($periodStart, $periodEnd, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовок отчета
            fputcsv($file, ['ОТЧЕТ ПО ПРОДАЖАМ'], ';');
            fputcsv($file, ['Период: ' . $dateFrom . ' - ' . $dateTo], ';');
            fputcsv($file, ['Дата формирования: ' . now()->format('d.m.Y H:i:s')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Общая статистика
            $totalRevenue = Order::whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('price_final') ?? 0;
            $totalSoldItems = OrderItem::whereHas('order', function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('created_at', [$periodStart, $periodEnd]);
            })
                ->sum('quantity') ?? 0;
            $totalOrders = Order::whereBetween('created_at', [$periodStart, $periodEnd])
                ->count();

            fputcsv($file, ['ОБЩАЯ СТАТИСТИКА'], ';');
            fputcsv($file, ['Показатель', 'Значение'], ';');
            fputcsv($file, ['Общая выручка', number_format($totalRevenue, 2, '.', '') . ' ₽'], ';');
            fputcsv($file, ['Количество проданных товаров', $totalSoldItems], ';');
            fputcsv($file, ['Количество заказов', $totalOrders], ';');
            fputcsv($file, ['Средний чек', $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2, '.', '') . ' ₽' : '0 ₽'], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Топ продаваемых товаров
            fputcsv($file, ['ТОП ПРОДАВАЕМЫХ ТОВАРОВ'], ';');
            fputcsv($file, ['Товар', 'Количество', 'Выручка'], ';');
            $topProducts = OrderItem::whereHas('order', function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('created_at', [$periodStart, $periodEnd]);
            })
                ->where('item_type', Product::class)
                ->join('products', 'products.id', '=', 'order_items.item_id')
                ->select('products.name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.price) as total_revenue'))
                ->groupBy('products.name')
                ->orderByDesc('total_quantity')
                ->limit(100)
                ->get();

            foreach ($topProducts as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->total_quantity,
                    number_format($product->total_revenue, 2, '.', '') . ' ₽'
                ], ';');
            }
            fputcsv($file, [], ';'); // Пустая строка

            // Продажи по дням
            fputcsv($file, ['ПРОДАЖИ ПО ДНЯМ'], ';');
            fputcsv($file, ['Дата', 'Количество заказов', 'Выручка', 'Количество товаров'], ';');
            $salesByDay = Order::whereBetween('created_at', [$periodStart, $periodEnd])
                ->selectRaw("
                    DATE(created_at) as day,
                    COUNT(*) as orders_count,
                    SUM(price_final) as revenue
                ")
                ->groupBy('day')
                ->orderBy('day')
                ->get();

            foreach ($salesByDay as $day) {
                $itemsCount = OrderItem::whereHas('order', function ($query) use ($day) {
                    $query->whereDate('created_at', $day->day);
                })
                    ->sum('quantity') ?? 0;

                fputcsv($file, [
                    $day->day,
                    $day->orders_count,
                    number_format($day->revenue ?? 0, 2, '.', '') . ' ₽',
                    $itemsCount
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

