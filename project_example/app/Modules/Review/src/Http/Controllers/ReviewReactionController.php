<?php

namespace Review\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Review\src\Enums\ReviewReactionTypeEnum;
use App\Modules\Review\src\Models\ReviewReaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;
use Review\Models\Review;

class ReviewReactionController extends Controller
{

    #[Endpoint('Поставить/снять лайк/дизлайк на отзыв (api)')]
    #[BodyParam('type', 'Тип реакции: like или dislike', required: true, example: 'like')]
    #[Response(description: 'Лайк/Дизлайк обновлён', status: 200)]
    public function toggle(Request $request, Review $review)
    {
        if (!auth()->check()) {
            return redirect()->to(
                url()->previous() . (str_contains(url()->previous(), '?') ? '&' : '?') . 'modal=login'
            );
        }

        $user = auth()->user();

        $request->validate([
            'type' => ['required', Rule::in([ReviewReactionTypeEnum::Like->value, ReviewReactionTypeEnum::Dislike->value])],
        ]);

        $type = $request->input('type');

        $reaction = ReviewReaction::where('user_id', $user->id)
            ->where('review_id', $review->id)
            ->first();

        if ($reaction) {
            if ($reaction->type === $type) {
                $reaction->delete();
                $message = "{$reaction->type} снят";
            } else {
                $reaction->update(['type' => $type]);
                $message = "Вы изменили реакцию на {$type}";
            }
        } else {
            ReviewReaction::create([
                'user_id' => $user->id,
                'review_id' => $review->id,
                'type' => $type,
            ]);
            $message = "{$type} поставлен";
        }

        return redirect()->back()->with(['success' => $message]);
    }

}
