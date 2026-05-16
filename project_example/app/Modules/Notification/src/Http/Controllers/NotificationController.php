<?php

namespace Notification\Http\Controllers;

use App\Http\Controllers\Controller;
use Alert\Models\Alert;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\UrlParam;


#[Group("Оповещения", "Методы для оповещений")]
class Notification extends Controller
{
    #[Endpoint("Пометить указанные уведомления как прочитанные")]
    #[BodyParam('alert_ids', 'array', 'Массив ID уведомлений для пометки как прочитанные', required: true, example: [1, 2, 3])]
    #[BodyParam('alert_ids.*', 'integer', 'ID уведомления', required: true, example: 1)]
    public function markAsRead(Request $request)
    {
        $request->validate([
            'alert_ids' => 'required|array',
            'alert_ids.*' => 'required|integer|exists:alerts,id'
        ], [
            'alert_ids.required' => 'Массив ID уведомлений обязателен для заполнения.',
            'alert_ids.*.required' => 'Каждый ID уведомления обязателен для заполнения.',
            'alert_ids.*.integer' => 'Каждый ID уведомления должен быть целым числом.',
            'alert_ids.*.exists' => 'Одно или несколько уведомлений не найдены.'
        ]);

        $userId = auth()->id();
        $alertIds = $request->input('alert_ids');
        if (empty($alertIds)) {
            return redirect()->back()->with([
                'message' => 'Не переданы ID уведомлений.'
            ]);
        }
        $alertsToUpdate = Alert::where('user_id', $userId)
            ->whereIn('id', $alertIds)
            ->where('is_read', false)
            ->get();
        if ($alertsToUpdate->isEmpty()) {
            return redirect()->back()->with([
                'message' => 'Нет непрочитанных уведомлений для обновления.'
            ]);
        }
        $alertIdsToUpdate = $alertsToUpdate->pluck('id')->toArray();
        Alert::where('user_id', $userId)
            ->whereIn('id', $alertIdsToUpdate)
            ->update(['is_read' => true]);

        return redirect()->back()->with([
            'message' => 'Уведомления помечены как прочитанные.',
            'updated_ids' => $alertIdsToUpdate
        ]);
    }

    #[Endpoint("Пометить уведомление как удаленное пользователем")]
    #[UrlParam('id', 'integer', 'ID уведомления', required: true, example: 1)]
    public function markAsDeleted(Request $request, int $alert)
    {
        $userId = auth()->id();
        $alertModel = Alert::where('user_id', $userId)->find($alert);

        if (!$alertModel) {
            return redirect()->back()->with([
                'message' => 'Уведомление не найдено или не принадлежит пользователю.',
                'messageType' => 'error' // Можно добавить тип сообщения для фронтенда
            ]);
        }

        if ($alertModel->user_deleted) {
            return redirect()->back()->with([
                'message' => 'Уведомление уже помечено как удаленное.',
                'messageType' => 'info'
            ]);
        }

        $alertModel->update(['user_deleted' => true]);

        return redirect()->back()->with([
            'message' => 'Уведомление помечено как удаленное.',
            'messageType' => 'success'
        ]);
    }
}
