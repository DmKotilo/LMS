<?php

use App\Modules\Order\src\Enums\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Order\Enums\OrderDeliveryTypeEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('combo_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('transaction_method_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('order_promocode_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('delivery_address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->foreignId('pickup_location_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('last_name')->nullable();//фамилия
            $table->string('second_name')->nullable();//отчество
            $table->date('delivery_date')->nullable();
            $table->string('delivery_time_from')->nullable();
            $table->string('delivery_time_to')->nullable();
            $table->string('promo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('comment')->nullable();
            $table->float('cart_sum')->nullable();
            $table->float('delivery_price')->nullable();
            $table->float('weight')->nullable();
            $table->float('quantity')->nullable();
            $table->float('personal_discount')->nullable();
            $table->float('promocode_discount')->nullable();
            $table->float('price_final')->nullable();


            $table->string('delivery_type')->default(OrderDeliveryTypeEnum::pickup);
            $table->string('status')->default(OrderStatusEnum::New->value); // статус по умолчанию
            $table->timestamps();
        });
    }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
