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
        Schema::create('recipe_selections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('order_number')->nullable();
            $table->boolean('is_displayed')->default(false);
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('mini_description')->nullable();
            $table->boolean('publish');
            $table->boolean('show_main')->default(false)->nullable();
            $table->integer('cooking_time_minutes');
            $table->integer('difficult');
            $table->integer('number_of_persons');
            $table->longText('content')->nullable();
            $table->json('ingredients_for_dish_json')->nullable();
            $table->json('ingredients_for_sauce_json')->nullable();
            $table->timestamps();
        });
        Schema::create('recipe_selection_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_selection_id')->constrained('recipe_selections')->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
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
        Schema::dropIfExists('recipes');
    }
};
