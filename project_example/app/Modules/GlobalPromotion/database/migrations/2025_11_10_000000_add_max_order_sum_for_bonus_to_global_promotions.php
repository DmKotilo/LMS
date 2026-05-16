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
        Schema::table('global_promotions', function (Blueprint $table) {
            $table->decimal('max_order_sum_for_bonus', 10, 2)->nullable()->after('min_order_sum_for_bonus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_promotions', function (Blueprint $table) {
            $table->dropColumn('max_order_sum_for_bonus');
        });
    }
};

