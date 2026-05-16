<?php

namespace App\Http\Controllers;

use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use Bonus\Models\UserBonusHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LoyaltyReportController extends Controller
{
    public function export(): StreamedResponse
    {
        $dateFromParam = request()->get('date_from', now()->subMonth()->startOfMonth()->format('Y-m-d'));
        $dateToParam = request()->get('date_to', now()->endOfMonth()->format('Y-m-d'));

        $periodStart = Carbon::parse($dateFromParam)->startOfDay();
        $periodEnd = Carbon::parse($dateToParam)->endOfDay();
        $reportDateFrom = $periodStart->toDateString();
        $reportDateTo = $periodEnd->toDateString();
        $now = now();

        $activeAccrualsQuery = UserBonusHistory::query()
            ->where('type', BonusTypeEnum::Accrual)
            ->where('status', BonusStatusEnum::Active)
            ->where('remaining_amount', '>', 0)
            ->where('active_date', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', $now);
            });

        // 1. Количество активных участников программы
        $activeParticipants = (clone $activeAccrualsQuery)
            ->distinct('user_id')
            ->count('user_id');

        // 2. Средний размер накопленных бонусов на счетах пользователей
        $userBalancesSubQuery = (clone $activeAccrualsQuery)
            ->selectRaw('user_id, SUM(remaining_amount) as total_bonus')
            ->groupBy('user_id');

        $avgBonusAmount = DB::query()
            ->fromSub($userBalancesSubQuery, 'user_balances')
            ->avg('total_bonus') ?? 0.0;

        // 3. Количество использованных бонусов за период
        $usedBonuses = UserBonusHistory::where('type', BonusTypeEnum::Withdrawal)
            ->where('status', BonusStatusEnum::Spent)
            ->whereBetween('created_at', [
                $periodStart,
                $periodEnd
            ])
            ->sum('amount');

        // Дополнительная статистика
        $totalAccrued = UserBonusHistory::where('type', BonusTypeEnum::Accrual)
            ->whereBetween('created_at', [
                $periodStart,
                $periodEnd
            ])
            ->sum('amount');

        $expiredBonuses = UserBonusHistory::where('type', BonusTypeEnum::Withdrawal)
            ->where('status', BonusStatusEnum::Expired)
            ->whereBetween('created_at', [
                $periodStart,
                $periodEnd
            ])
            ->sum('amount');

        $filename = 'loyalty_report_' . date('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($activeParticipants, $avgBonusAmount, $usedBonuses, $totalAccrued, $expiredBonuses, $reportDateFrom, $reportDateTo) {
            $file = fopen('php://output', 'w');
            
            // BOM для корректного отображения кириллицы в Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Заголовок отчета
            fputcsv($file, ['ОТЧЕТ ПО СИСТЕМЕ ЛОЯЛЬНОСТИ'], ';');
            fputcsv($file, ['Период: ' . $reportDateFrom . ' - ' . $reportDateTo], ';');
            fputcsv($file, ['Дата формирования: ' . now()->format('d.m.Y H:i:s')], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Основные показатели
            fputcsv($file, ['ОСНОВНЫЕ ПОКАЗАТЕЛИ'], ';');
            fputcsv($file, ['Показатель', 'Значение'], ';');
            fputcsv($file, [
                'Количество активных участников программы',
                $activeParticipants
            ], ';');
            fputcsv($file, [
                'Средний размер накопленных бонусов на счетах пользователей',
                number_format((float) ($avgBonusAmount ?? 0), 2, '.', '')
            ], ';');
            fputcsv($file, [
                'Количество использованных бонусов за период',
                number_format((float) $usedBonuses, 2, '.', '')
            ], ';');
            fputcsv($file, [], ';'); // Пустая строка

            // Дополнительная статистика
            fputcsv($file, ['ДОПОЛНИТЕЛЬНАЯ СТАТИСТИКА'], ';');
            fputcsv($file, ['Показатель', 'Значение'], ';');
            fputcsv($file, [
                'Всего начислено бонусов за период',
                number_format((float) $totalAccrued, 2, '.', '')
            ], ';');
            fputcsv($file, [
                'Сгорело бонусов за период',
                number_format((float) $expiredBonuses, 2, '.', '')
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

