<?php

namespace Review\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Modules\Review\src\Enums\ReviewStatusEnum;
use Article\Models\Article;
use Carbon\Carbon;
use Combo\Models\Combo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Review\Models\Review;

#[Group("Отзывы в профиле", "Методы для работы с отзывами пользователя")]
class ReviewController extends Controller
{

    #[Endpoint("Создать отзыв")]
    #[BodyParam("item_type", "string", "Тип сущности (Product или Recipe или Combo Или Article)", required: true, example: "Recipe")]
    #[BodyParam("item_id", "integer", "ID сущности (рецепта или продукта или комбо или статьи)", required: true, example: 5)]
    #[BodyParam("rating", "integer", "Оценка от 1 до 5", required: true, example: 4)]
    #[BodyParam("text", "string", "Текст отзыва", required: true, example: "Отличный рецепт!")]
    #[BodyParam("images", "file[]", "Изображения (максимум 8 файлов)", required: false)]
    public function store(Request $request)
    {
        $user = auth()->user();

        // Проверяем, не превысил ли пользователь лимт отзывов за месяц
        $reviewsCount = Review::where('user_id', $user->id)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->count();

        if ($reviewsCount >= 3) {
            return back()->with('error', 'Вы уже оставили 3 отзыва в этом месяце. Попробуйте снова в следующем месяце.');
        }

        $validated = $request->validate([
            'item_type' => ['required', Rule::in(['Product', 'Recipe','Combo','Article'])],
            'item_id'   => 'required|integer',
            'rating'    => 'required|integer|min:1|max:5',
            'text'      => 'required|string|max:1000',
            'images'    => 'array|max:8',
            'images.*'  => 'file|image',
        ]);

        $modelClass = match ($validated['item_type']) {
            'Product' => \Product\Models\Product::class,
            'Recipe'  => \Recipe\Models\Recipe::class,
            'Article'  => Article::class,
            'Combo'  => Combo::class,
        };

        if (!$modelClass::where('id', $validated['item_id'])->exists()) {
            abort(422, "Некорректный item_type или item_id");
        }

        $review = Review::create([
            'user_id'   => $user->id,
            'item_type' => $modelClass,
            'item_id'   => $validated['item_id'],
            'rating'    => $validated['rating'],
            'text'      => $validated['text'],
            'status'    => ReviewStatusEnum::Pending,
        ]);

        if ($request->hasFile('images')) {
            $this->saveImages($review, $request->file('images'));
        }

        return back()->with('success', 'Отзыв отправлен на модерацию');
    }

//    public function store(Request $request)
//    {
//        $validated = $request->validate([
//            'item_type' => ['required', Rule::in(['Product', 'Recipe','Combo','Article'])],
//            'item_id'   => 'required|integer',
//            'rating'    => 'required|integer|min:1|max:5',
//            'text'      => 'required|string|max:1000',
//            'images'    => 'array|max:8',
//            'images.*'  => 'file|image',
//        ]);
//
//        $modelClass = match ($validated['item_type']) {
//            'Product' => \Product\Models\Product::class,
//            'Recipe'  => \Recipe\Models\Recipe::class,
//            'Article'  => Article::class,
//            'Combo'  => Combo::class,
//        };
//
//        if (!$modelClass::where('id', $validated['item_id'])->exists()) {
//            abort(422, "Некорректный item_type или item_id");
//        }
//
//        $review = Review::create([
//            'user_id'   => auth()->id(),
//            'item_type' => $modelClass,
//            'item_id'   => $validated['item_id'],
//            'rating'    => $validated['rating'],
//            'text'      => $validated['text'],
//            'status'    => ReviewStatusEnum::Pending,
//        ]);
//
//        if ($request->hasFile('images')) {
//            $this->saveImages($review, $request->file('images'));
//        }
//
//        return back()->with('success', 'Отзыв отправлен на модерацию');
//    }


    #[Endpoint("Обновить отзыв")]
    #[BodyParam("rating", "integer", "Оценка от 1 до 5", required: true, example: 4)]
    #[BodyParam("text", "string", "Текст отзыва", required: true, example: "Отличный рецепт!")]
    #[BodyParam("images", "array", "Изображения макс:8 (пути к уже загруженным(string) или новые файлы)", required: false)]
   public function update(Request $request, Review $review)
   {
           if ($review->user_id !== auth()->id()) {
               abort(403);
           }
           $validated = $request->validate([
               'rating' => 'required|integer|min:1|max:5',
               'text' => 'required|string|max:1000',
               'images.*' => 'nullable',
           ]);
           $review->update([
               'rating' => $validated['rating'],
               'text' => $validated['text'],
           ]);
           $keepIds = [];
           $existingIds = $request->input('images', []);
           foreach ($existingIds as $id) {
               if (is_numeric($id)) {
                   $keepIds[] = (int) $id;
               }
           }
           $newFiles = $request->file('images', []);
           if (count($keepIds) + count($newFiles) > 8) {
               abort(422, 'Максимум 8 изображений');
           }
           $actualCount = $review->images()->whereIn('id', $keepIds)->count();
           if ($actualCount != count($keepIds)) {
               abort(422, 'Некорректные ID изображений');
           }
           foreach ($review->images as $image) {
               if (!in_array($image->id, $keepIds)) {
                   Storage::disk('public')->delete($image->path);
                   $image->delete();
               }
           }
           if ($newFiles) {
               $this->saveImages($review, $newFiles);
           }
           return back()->with('success', 'Отзыв успешно редактирован');
   }
    #[Endpoint("Удалить отзыв")]
    public function destroy(Review $review)
    {
        if ($review->user_id !== auth()->id()) {
            abort(403);
        }

        $review->update([
            'status' => ReviewStatusEnum::UserDeleted,
            'show_main' => false,
            'show_catalog' => false,
            'show_about_us' => false,
        ]);

        return back()->with('success', 'Отзыв удален');
    }

    private function saveImages(Review $review, array $files)
    {
        $modelName = class_basename($review);
        $dir = "{$modelName}/{$review->id}/image";

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();

            if ($review->images()->where('file_name', $originalName)->exists()) {
                continue;
            }

            $file->storeAs($dir, $originalName, 'public');

            $review->images()->create([
                'file_name'     => $originalName,
                'type_relation' => 'images',
                'extension'     => $file->getClientOriginalExtension(),
                'size'          => $file->getSize(),
                'disk'          => 'public',
                'path'          => "{$dir}/{$originalName}",
            ]);
        }
    }

}
