<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('combos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('uuid_1c')->nullable();
            $table->string('name_1c')->nullable();
            $table->float('price');
            $table->string('price_type');
//            $table->float('sale_price')->nullable();
            $table->string('article_number')->nullable();
            $table->text('description')->nullable();
            $table->string('short_description')->nullable();
            $table->integer('quantity');
            $table->float('weight');
            $table->string('weight_type');
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->boolean('show_main')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
