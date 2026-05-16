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
        Schema::create('global_promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->unsignedInteger('bonus_points')->nullable();
            $table->decimal('min_order_sum_for_bonus', 10, 2)->nullable();

            $table->decimal('cashback_percent', 5, 2)->nullable();
            $table->decimal('min_order_sum_for_cashback', 10, 2)->nullable();

            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->text('keywords')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
