<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_bonus_history', function (Blueprint $table) {
            // Дата истечения бонусов (active_date + 90 дней для начислений)
            $table->datetime('expires_at')->nullable();
            
            // Остаток количества бонусов
            $table->decimal('remaining_amount', 10, 2)->nullable();
                        
        });
    }

    public function down(): void
    {
        Schema::table('user_bonus_history', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'remaining_amount']);
        });
    }
};

