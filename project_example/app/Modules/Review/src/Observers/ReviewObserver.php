<?php

namespace Review\Observers;

use Review\Models\Review;
use Bonus\Services\BonusService;
use Setting\Models\Setting;
use App\Notifications\ReviewBonusNotification;
use Alert\Models\Alert;
use Alert\Enums\AlertTypeEnum;
use Illuminate\Support\Facades\Log;

class ReviewObserver
{
    protected BonusService $bonusService;

    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    public function updated(Review $review): void
    {
        if ($review->isDirty('status') &&
            $review->status === \App\Modules\Review\src\Enums\ReviewStatusEnum::Approved &&
            $review->getOriginal('status') !== \App\Modules\Review\src\Enums\ReviewStatusEnum::Approved) {

            $this->awardBonus($review);
        }
    }

    protected function awardBonus(Review $review): void
    {
        $bonusAmount = Setting::where('key', 'review_bonus')->first();
        $bonusValue = $bonusAmount ? $bonusAmount->value : 0;

        if ($bonusValue > 0) {
            $bonusHistory = $this->bonusService->accrueBonus(
                user: $review->user,
                amount: $bonusValue,
                comment: 'Бонус за одобренный отзыв'
            );

            // Отправка email
            if ($review->user->email && $review->user->email_verified_at) {
                $review->user->notify(new ReviewBonusNotification($bonusValue));
            }

            // Создание уведомления в системе
            Alert::create([
                'user_id' => $review->user->id,
                'type' => AlertTypeEnum::ApprovedReview,
                'title' => 'alerts.reviewBonusTitle',
                'message' => "alerts.reviewBonusMessage",
                'button_text' => 'alerts.buttonDetails',
                'button_link' => route('user.profile.bonus.history'),
                'bonus_count' => $bonusValue,
                'bonus_message' => "alerts.reviewBonusCount",
                'bonus_date' => now()->addDays(90)->format('d.m.Y'),
                'is_read' => false,
            ]);
        }
    }
}
