<?php

namespace App\Http\Controllers;

use Product\Models\Product;
use Review\Models\Review;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReviewsExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $dateFrom = request()->query('date_from', request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d')));
        $dateTo = request()->query('date_to', request()->get('date_to', now()->endOfMonth()->format('Y-m-d')));

        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $reviews = Review::where('item_type', Product::class)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with(['user', 'item'])
            ->get();

        $filename = 'reviews_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($reviews, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовок отчета
            fputcsv($file, ['ОТЧЕТ ПО ОТЗЫВАМ И РЕЙТИНГАМ'], ';');
            fputcsv($file, ['Период: ' . $dateFrom . ' - ' . $dateTo], ';');
            fputcsv($file, ['Дата формирования: ' . now()->format('d.m.Y H:i:s')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Общая статистика
            $totalReviews = $reviews->count();
            $avgRating = $reviews->whereNotNull('rating')->avg('rating') ?? 0;
            
            fputcsv($file, ['ОБЩАЯ СТАТИСТИКА'], ';');
            fputcsv($file, ['Показатель', 'Значение'], ';');
            fputcsv($file, ['Количество отзывов за период', $totalReviews], ';');
            fputcsv($file, ['Средний рейтинг', number_format((float) $avgRating, 2, '.', '')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Самые популярные товары по количеству отзывов
            $productsByReviews = $reviews->groupBy(function ($review) {
                return $review->item ? $review->item->name : 'Товар удален';
            })->map(function ($group) {
                return $group->count();
            })->sortDesc()->take(20);

            fputcsv($file, ['САМЫЕ ПОПУЛЯРНЫЕ ТОВАРЫ ПО КОЛИЧЕСТВУ ОТЗЫВОВ'], ';');
            fputcsv($file, ['Товар', 'Количество отзывов'], ';');
            foreach ($productsByReviews as $productName => $count) {
                fputcsv($file, [$productName, $count], ';');
            }
            fputcsv($file, [], ';'); // Пустая строка

            // Детальные данные
            fputcsv($file, ['ДЕТАЛЬНЫЕ ДАННЫЕ'], ';');
            fputcsv($file, [
                'ID',
                'Пользователь',
                'Email пользователя',
                'Товар',
                'Рейтинг',
                'Текст отзыва',
                'Статус',
                'Дата создания'
            ], ';');

            // Данные
            foreach ($reviews as $review) {
                $productName = $review->item ? $review->item->name : 'Товар удален';
                $userName = $review->user ? $review->user->name : 'Пользователь удален';
                $userEmail = $review->user ? $review->user->email : '';
                
                fputcsv($file, [
                    $review->id,
                    $userName,
                    $userEmail,
                    $productName,
                    $review->rating ?? '',
                    $review->text ?? '',
                    $review->status->toString(),
                    $review->created_at->format('d.m.Y H:i:s')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

