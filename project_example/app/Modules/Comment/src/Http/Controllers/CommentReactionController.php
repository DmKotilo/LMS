<?php

namespace Comment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Comment\src\Enums\CommentReactionTypeEnum;
use App\Modules\Comment\src\Models\CommentReaction;
use Comment\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Response;

class CommentReactionController extends Controller
{
    #[Endpoint('Поставить/снять лайк/дизлайк на комментарий (api)')]
    #[BodyParam('type', 'Тип реакции: like или dislike', required: true, example: 'like')]
    #[Response(description: 'Лайк/Дизлайк обновлён', status: 200)]
    public function toggle(Request $request, Comment $comment)
    {
        if (!auth()->check()) {
            return redirect()->to(
                url()->previous() . (str_contains(url()->previous(), '?') ? '&' : '?') . 'modal=login'
            );
        }

        $user = auth()->user();

        $request->validate([
            'type' => ['required', Rule::in([CommentReactionTypeEnum::Like->value, CommentReactionTypeEnum::Dislike->value])],
        ]);

        $type = $request->input('type');

        $reaction = CommentReaction::where('user_id', $user->id)
            ->where('comment_id', $comment->id)
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
            CommentReaction::create([
                'user_id' => $user->id,
                'comment_id' => $comment->id,
                'type' => $type,
            ]);
            $message = "{$type} поставлен";
        }

        return redirect()->back()->with(['success' => $message]);
    }
}

