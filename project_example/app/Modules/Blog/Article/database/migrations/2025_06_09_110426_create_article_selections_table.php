<?php

use Category\Models\Category;
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
        Schema::create('article_selections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('order_number')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->integer('views_count')->default(0);
            $table->boolean('is_displayed')->default(false);
            $table->timestamps();
        });
        Schema::create('article_selection_article', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_selection_id')->constrained('article_selections')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->integer('order_number')->nullable();
            $table->boolean('is_displayed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_selections');
        Schema::dropIfExists('article_selection_article');
    }
};
