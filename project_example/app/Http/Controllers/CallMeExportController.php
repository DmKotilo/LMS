<?php

namespace App\Http\Controllers;

use ContactForm\Models\CallContactForm;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CallMeExportController extends Controller
{
    public function export(): StreamedResponse
    {
        $dateFrom = request()->get('date_from', now()->subWeek()->startOfDay()->format('Y-m-d'));
        $dateTo = request()->get('date_to', now()->endOfDay()->format('Y-m-d'));

        $periodStart = \Carbon\Carbon::parse($dateFrom)->startOfDay();
        $periodEnd = \Carbon\Carbon::parse($dateTo)->endOfDay();

        $requests = CallContactForm::whereBetween('created_at', [$periodStart, $periodEnd])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'call_me_export_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($requests, $dateFrom, $dateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовок отчета
            fputcsv($file, ['ОТЧЕТ ПО ЗАЯВКАМ "ПОЗВОНИТЬ МНЕ"'], ';');
            fputcsv($file, ['Период: ' . $dateFrom . ' - ' . $dateTo], ';');
            fputcsv($file, ['Дата формирования: ' . now()->format('d.m.Y H:i:s')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Заголовки
            fputcsv($file, [
                'ID',
                'Имя клиента',
                'Номер телефона',
                'Email',
                'Комментарий',
                'Интервал звонка',
                'Должность',
                'Статус',
                'Тип заявки',
                'Дата создания'
            ], ';');

            // Данные
            foreach ($requests as $request) {
                fputcsv($file, [
                    $request->id,
                    $request->name ?? '',
                    $request->phone ?? '',
                    $request->email ?? '',
                    $request->comment ?? '',
                    $request->time_interval ?? '',
                    $request->post ?? '',
                    $request->status?->toString() ?? '',
                    $request->type?->toString() ?? '',
                    $request->created_at->format('d.m.Y H:i:s')
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

