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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->morphs('item');
            $table->text('text');
            $table->integer('rating');
            $table->string('status')->default(\App\Modules\Review\src\Enums\ReviewStatusEnum::Pending);
            $table->boolean('show_main')->default(false);
            $table->boolean('show_catalog')->default(false);
            $table->boolean('show_about_us')->default(false);
            $table->boolean('show_promotion_page')->default(false);
            $table->boolean('show_combo_page')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
