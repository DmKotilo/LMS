<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->integer('active_cooking_time_minutes')->nullable(); // Добавляем новое поле
            $table->text('description')->nullable()->change();         // Изменяем тип существующего поля
        });
    }

    public function down()
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('active_cooking_time_minutes'); // Удаляем новое поле
            $table->string('description')->nullable()->change(); // Возвращаем тип существующего поля
        });
    }
};
