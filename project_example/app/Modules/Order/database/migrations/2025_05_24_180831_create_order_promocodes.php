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
        Schema::create('order_promocodes', function (Blueprint $table) {
            $table->id();
            $table->string('promocode');
            $table->string('description')->nullable(); // для админа
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('exceeded_limit')->nullable(); //лимит применений
            $table->integer('number_applications')->nullable()->default(0); //количество применений на текущий момент
            $table->string('percent')->nullable(); //процент скидки
            $table->string('status')->default(\Order\Enums\OrderPromocodeStatusEnum::active->value);
            $table->timestamps();
        });
    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_promocodes');
    }
};
