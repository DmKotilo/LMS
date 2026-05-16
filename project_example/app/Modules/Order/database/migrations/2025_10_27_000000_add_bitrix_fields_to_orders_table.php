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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('bitrix_lead_id')->nullable()->comment('ID лида в Битрикс24');
            $table->string('bitrix_deal_id')->nullable()->comment('ID сделки в Битрикс24');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['bitrix_lead_id', 'bitrix_deal_id']);
        });
    }
};
