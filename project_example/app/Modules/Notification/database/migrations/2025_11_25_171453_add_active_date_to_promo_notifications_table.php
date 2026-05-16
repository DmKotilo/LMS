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
        Schema::table('promo_notifications', function (Blueprint $table) {
            $table->timestamp('active_date')->nullable()->after('user_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_notifications', function (Blueprint $table) {
            $table->dropColumn('active_date');
        });
    }
};

