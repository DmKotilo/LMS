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
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->float('min_order_sum')->default(0);
            $table->float('delivery_price')->default(0);
            $table->tinyInteger('delivery_time')->nullable();
            $table->longText('description')->nullable();
            $table->json('time_intervals')->nullable();
            $table->timestamps();
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_zones');
    }
};
