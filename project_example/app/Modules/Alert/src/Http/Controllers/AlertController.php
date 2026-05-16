<?php

namespace Alert\Http\Controllers;

use App\Http\Controllers\Controller;
use Alert\Models\Alert;
use Notification\Models\PromoNotification;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\UrlParam;


#[Group("Оповещения", "Методы для оповещений")]
class AlertController extends Controller
{
    #[Endpoint("Пометить указанные уведомления как прочитанные")]
    #[BodyParam('type', 'string', 'Тип уведомления (alert, оповещения/notify, новинки и промоакции)', required: true, example: 'alert')]
    #[BodyParam('ids', 'array', 'Массив ID уведомлений для пометки как прочитанные', required: true, example: [1, 2, 3])]
    #[BodyParam('ids.*', 'integer', 'ID уведомления', required: true, example: 1)]
    public function markAsRead(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:alert,notify',
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer'
        ]);

        $userId = auth()->id();
        $notificationIds = $request->input('ids');
        $type = $request->input('type');

        if ($type === 'alert') {
            $notificationsToUpdate = Alert::where('user_id', $userId)
                ->whereIn('id', $notificationIds)
                ->where(function ($query) {
                    $query->where('is_read', false)
                        ->orWhereNull('is_read');
                })
                ->get();

            if ($notificationsToUpdate->isEmpty()) {
//                return response()->json(['message' => 'Нет непрочитанных уведомлений для обновления.'], 400);
                return redirect()->back()->with([
                    'message' => 'Нет непрочитанных уведомлений для обновления.'
                ]);
            }

            $notificationIdsToUpdate = $notificationsToUpdate->pluck('id')->toArray();
            $updatedCount = Alert::where('user_id', $userId)
                ->whereIn('id', $notificationIdsToUpdate)
                ->update(['is_read' => true]);

//            return response()->json([
//                'message' => 'Уведомления помечены как прочитанные.',
//                'updated_ids' => $notificationIdsToUpdate,
//                'updated_count' => $updatedCount
//            ]);
            return redirect()->back()->with('success', 'Уведомления помечены как прочитанные');
        } else {
            $notificationsToUpdate = PromoNotification::where('user_id', $userId)
                ->whereIn('id', $notificationIds)
                ->where(function ($query) {
                    $query->where('is_read', false)
                        ->orWhereNull('is_read');
                })
                ->get();

            if ($notificationsToUpdate->isEmpty()) {
//                return response()->json(['message' => 'Нет непрочитанных уведомлений для обновления.'], 400);
                return redirect()->back()->with([
                    'message' => 'Нет непрочитанных уведомлений для обновления.'
                ]);
            }

            $notificationIdsToUpdate = $notificationsToUpdate->pluck('id')->toArray();
            $updatedCount = PromoNotification::where('user_id', $userId)
                ->whereIn('id', $notificationIdsToUpdate)
                ->update(['is_read' => true]);

            return redirect()->back()->with('success', 'Уведомления помечены как прочитанные');

//            return response()->json([
//                'message' => 'Уведомления помечены как прочитанные.',
//                'updated_ids' => $notificationIdsToUpdate,
//                'updated_count' => $updatedCount
//            ]);
        }
    }

    #[Endpoint("Пометить уведомление как удаленное пользователем")]
    #[BodyParam('type', 'string', 'Тип уведомления (alert, оповещения/notify, новинки и промоакции)', required: true, example: 'alert')]
    #[BodyParam('id', 'integer', 'ID уведомления', required: true, example: 1)]
    public function markAsDeleted(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:alert,notify',
            'id' => 'required|integer'
        ]);

        $userId = auth()->id();
        $notificationId = $request->input('id');
        $type = $request->input('type');

        if ($type === 'alert') {
            $notificationModel = Alert::where('user_id', $userId)->find($notificationId);

            if (!$notificationModel) {
                return response()->json([
                    'message' => 'Уведомление не найдено или не принадлежит пользователю.',
                    'messageType' => 'error'
                ], 400);
            }

            if ($notificationModel->user_deleted) {
                return response()->json([
                    'message' => 'Уведомление уже помечено как удаленное.',
                    'messageType' => 'info'
                ], 400);
            }

            $notificationModel->update(['user_deleted' => true]);
        } else {
            $notificationModel = PromoNotification::where('user_id', $userId)->find($notificationId);

            if (!$notificationModel) {
                return response()->json([
                    'message' => 'Уведомление не найдено или не принадлежит пользователю.',
                    'messageType' => 'error'
                ], 400);
            }

            $notificationModel->update(['user_deleted' => true]);
        }

        return redirect()->back()->with('success', 'Уведомление помечено как удаленное');
//        return response()->json([
//            'message' => 'Уведомление помечено как удаленное.',
//            'messageType' => 'success'
//        ]);
    }
}
