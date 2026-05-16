<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('uuid_1c')->nullable();
            $table->string('degree_type')->nullable();
            $table->string('article_number')->nullable();
//            $table->integer('quantity');
            $table->float('weight')->nullable();
            $table->string('weight_type')->nullable();
            $table->string('name');
            $table->string('name_1c')->nullable();
            $table->float('price')->nullable();
            $table->string('price_type')->nullable();
            $table->text('description')->nullable();
            $table->text('description_1с')->nullable();
            $table->string('category_1c')->nullable();
            $table->string('subcategory_1c')->nullable();
            $table->string('short_description')->nullable();
//            $table->float('sale_price')->nullable();
            $table->text('keywords')->nullable();
            $table->foreignId('manufacturer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('preview_images')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_new')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
