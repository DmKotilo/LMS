<?php

namespace Review\Database\Seeders;
use App\Modules\Cart\src\Enums\CartItemTypeEnum;
use App\Modules\Cart\src\Enums\CartStatusEnum;
use App\Modules\Cart\src\Models\CartItem;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\Modules\Review\src\Models\ReviewAnswer;
use Cart\Models\UserCart;
use Combo\Models\Combo;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Review\Models\Review;

class ReviewSeeder extends Seeder
{
    protected function attachImagesToReview(Review $review, int $id): array
    {
        $sourcePath = public_path("images/Seeder/Review/{$id}/image/");

        if (!file_exists($sourcePath)) {

            $id = rand(1, 14);
            $sourcePath = public_path("images/Seeder/Review/{$id}/image/");
        }

        $files = glob("{$sourcePath}{$id}*.webp");
        $previewImages = [];

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $storagePath = "Review/{$review->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($filePath));

            File::create([
                'fileable_id' => $review->id,
                'fileable_type' => Review::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'images',
                'extension' => 'webp',
                'size' => filesize($filePath),
                'disk' => 'public',
            ]);

        }

        return $previewImages;
    }
    public function run()
    {
        $adminId = 1;

        foreach (Product::limit(10)->get() as $product) {
            $review = Review::create([
                'user_id'   => rand(1, 2),
                'item_type' => Product::class,
                'item_id'   => $product->id,
                'text'      => 'Отзыв на продукт ' . $product->name,
                'rating'    => rand(1, 5),
                'status'    => ReviewStatusEnum::Approved,
                'show_main' => true,
                'show_catalog' => true,
                'show_about_us' => true,
                'show_promotion_page' => true,
                'show_combo_page' => true,
            ]);

             $this->attachImagesToReview($review, $review->id);

            ReviewAnswer::create([
                'review_id' => $review->id,
                'admin_id'  => $adminId,
                'text'      => 'Спасибо за отзыв!',
                'is_active' => true,
            ]);
        }

        foreach (Recipe::limit(10)->get() as $recipe) {
            $review = Review::create([
                'user_id'   => rand(1, 2),
                'item_type' => Recipe::class,
                'item_id'   => $recipe->id,
                'text'      => 'Отзыв на рецепт ' . $recipe->title,
                'rating'    => rand(1, 5),
                'status'    => ReviewStatusEnum::Approved,
                'show_main' => true,
                'show_catalog' => true,
                'show_about_us' => true,
            ]);
            $this->attachImagesToReview($review, $review->id);

            ReviewAnswer::create([
                'review_id' => $review->id,
                'admin_id'  => $adminId,
                'text'      => 'Спасибо за отзыв на рецепт!',
                'is_active' => true,
            ]);
        }
        foreach (Combo::limit(6)->get() as $combo) {
            $review = Review::create([
                'user_id'   => rand(1, 2),
                'item_type' => Combo::class,
                'item_id'   => $combo->id,
                'text'      => 'Отзыв на рецепт ' . $combo->name,
                'rating'    => rand(1, 5),
                'status'    => ReviewStatusEnum::Approved,
                'show_main' => true,
                'show_catalog' => true,
                'show_about_us' => true,
            ]);
            $this->attachImagesToReview($review, $review->id);

            ReviewAnswer::create([
                'review_id' => $review->id,
                'admin_id'  => $adminId,
                'text'      => 'Спасибо за отзыв на комбо!',
                'is_active' => true,
            ]);
        }
    }



}
