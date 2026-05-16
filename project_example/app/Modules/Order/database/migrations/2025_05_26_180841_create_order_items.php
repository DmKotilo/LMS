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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->morphs('item');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->float('price');//общая цена
            $table->float('price_for_unit');// цена за единиуц
            $table->float('price_for_unit_without_sale');// цена за единицу без скидки
            $table->float('unit_sale_percent');//скидка за единицу товара (перемножать не надо на количество)
            $table->float('weight')->nullable();//общий вес
            $table->float('weight_for_unit')->nullable();// вес за единицу
            $table->string('weight_type')->nullable();
            $table->unsignedInteger('quantity')->default(1);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
