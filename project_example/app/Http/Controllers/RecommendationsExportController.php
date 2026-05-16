<?php

namespace App\Http\Controllers;

use Analitic\Models\Analitic;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecommendationsExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $dateFrom = request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateTo = request()->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $filename = 'recommendations_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($periodStart, $periodEnd, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовок отчета
            fputcsv($file, ['ОТЧЕТ ПО БЛОКУ РЕКОМЕНДАЦИЙ'], ';');
            fputcsv($file, ['Период: ' . $dateFrom . ' - ' . $dateTo], ';');
            fputcsv($file, ['Дата формирования: ' . now()->format('d.m.Y H:i:s')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Общая статистика
            $totalClicks = Analitic::whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('recomend_click') ?? 0;
            $totalAddsToCart = Analitic::whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('recomend_to_cart') ?? 0;

            fputcsv($file, ['ОБЩАЯ СТАТИСТИКА'], ';');
            fputcsv($file, ['Показатель', 'Значение'], ';');
            fputcsv($file, ['Количество кликов по рекомендованным продуктам', $totalClicks], ';');
            fputcsv($file, ['Количество добавлений в корзину из блока рекомендаций', $totalAddsToCart], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Топ продуктов по кликам
            fputcsv($file, ['ТОП ПРОДУКТОВ ПО КЛИКАМ'], ';');
            fputcsv($file, ['Товар', 'Количество кликов'], ';');
            $topProductsByClicks = DB::table('analitics')
                ->join('products', 'products.id', '=', 'analitics.product_id')
                ->whereBetween('analitics.created_at', [$periodStart, $periodEnd])
                ->select('products.name', DB::raw('SUM(analitics.recomend_click) as total_clicks'))
                ->groupBy('products.name')
                ->orderByDesc('total_clicks')
                ->limit(50)
                ->get();

            foreach ($topProductsByClicks as $product) {
                fputcsv($file, [$product->name, $product->total_clicks], ';');
            }
            fputcsv($file, [], ';'); // Пустая строка

            // Топ продуктов по добавлениям в корзину
            fputcsv($file, ['ТОП ПРОДУКТОВ ПО ДОБАВЛЕНИЯМ В КОРЗИНУ'], ';');
            fputcsv($file, ['Товар', 'Количество добавлений'], ';');
            $topProductsByCart = DB::table('analitics')
                ->join('products', 'products.id', '=', 'analitics.product_id')
                ->whereBetween('analitics.created_at', [$periodStart, $periodEnd])
                ->select('products.name', DB::raw('SUM(analitics.recomend_to_cart) as total_adds'))
                ->groupBy('products.name')
                ->orderByDesc('total_adds')
                ->limit(50)
                ->get();

            foreach ($topProductsByCart as $product) {
                fputcsv($file, [$product->name, $product->total_adds], ';');
            }
            fputcsv($file, [], ';'); // Пустая строка

            // Популярные комбинации
            fputcsv($file, ['ПОПУЛЯРНЫЕ КОМБИНАЦИИ ТОВАРОВ'], ';');
            fputcsv($file, ['Комбинация', 'Количество'], ';');
            $popularCombinations = DB::table('cart_products as cp1')
                ->join('cart_products as cp2', function ($join) {
                    $join->on('cp1.cart_id', '=', 'cp2.cart_id')
                        ->whereRaw('cp1.product_id < cp2.product_id');
                })
                ->join('products as p1', 'p1.id', '=', 'cp1.product_id')
                ->join('products as p2', 'p2.id', '=', 'cp2.product_id')
                ->whereBetween('cp1.created_at', [$periodStart, $periodEnd])
                ->selectRaw("
                    CONCAT(p1.name, ' + ', p2.name) AS combination,
                    COUNT(*) AS total
                ")
                ->groupBy('combination')
                ->orderByDesc('total')
                ->limit(50)
                ->get();

            foreach ($popularCombinations as $combination) {
                fputcsv($file, [$combination->combination, $combination->total], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

