<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Выберите все продукты, у которых slug пустой или NULL
        $products = DB::table('products')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->select('id', 'name') // предполагается, что имя находится в поле 'name'
            ->get();

        foreach ($products as $product) {
            $slug = Str::slug($product->name);

            $originalSlug = $slug;
            $counter = 1;

            while (DB::table('products')
                ->where('slug', $slug) // используем =, что в PostgreSQL чувствительно к регистру
                ->where('id', '!=', $product->id)
                ->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            DB::table('products')
                ->where('id', $product->id)
                ->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // В обратной миграции вы можете, например, установить все слаги в NULL
        // DB::table('products')->update(['slug' => null]);
        // Но обычно это не требуется.
    }
};
