<?php

namespace Comment\Http\Controllers;

use App\Http\Controllers\Controller;
use Comment\Models\Comment;
use Comment\Enums\CommentStatusEnum;
use Article\Models\Article;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Combo\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

#[Group("Комментарии", "Методы для работы с комментариями")]
class CommentController extends Controller
{
     #[Endpoint("Создать комментарий")]
     #[BodyParam("item_type", "string", "Тип сущности (например, Article, Product, Recipe, Combo)", required: true, example: "Article")]
     #[BodyParam("item_id", "integer", "ID сущности", required: true, example: 1)]
     #[BodyParam("text", "string", "Текст комментария", required: true, example: "Отличная статья!")]
    public function store(Request $request): \Illuminate\Http\RedirectResponse
     {
        $validated = $request->validate([
            'item_type' => ['required', 'string', Rule::in(['Article', 'Product', 'Recipe', 'Combo'])], // Добавьте другие типы при необходимости
            'item_id'   => 'required|integer',
            'text'      => 'required|string|max:1000',
        ]);

        $modelClass = $this->getModelClass($validated['item_type']);

        if (!$modelClass || !$modelClass::where('id', $validated['item_id'])->exists()) {
            return redirect()->back()->with('error','Указанная сущность (item_type/item_id) не найдена');

        }

        // 4. Создание комментария
        $comment = Comment::create([
            'user_id'   => auth()->id(),
            'item_type' => $modelClass,
            'item_id'   => $validated['item_id'],
            'text'      => $validated['text'],
            'status'    => CommentStatusEnum::Pending,
        ]);

        // 5. Возвращаем успешный ответ
        return redirect()->back()->with('success','Комментарий успешно создан и отправлен на модерацию');
    }

    private function getModelClass(string $type): ?string
    {
        return match ($type) {
            'Article' => Article::class,
            'Product' => Product::class,
            'Recipe'  => Recipe::class,
            'Combo'   => Combo::class,
            default => null,
        };
    }
}
