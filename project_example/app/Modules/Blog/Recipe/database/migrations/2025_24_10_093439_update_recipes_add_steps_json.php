<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Удаляем таблицу recipe_steps
        Schema::dropIfExists('recipe_steps');

        // Добавляем поле recipe_steps_json в таблицу recipes
        Schema::table('recipes', function (Blueprint $table) {
            $table->json('recipe_steps_json')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        // Восстанавливаем таблицу recipe_steps
        Schema::create('recipe_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('order_number')->nullable();
            $table->timestamps();
        });

        // Удаляем поле recipe_steps_json
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('recipe_steps_json');
        });
    }
};
