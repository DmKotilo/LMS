<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_bonus_history', function (Blueprint $table) {
            $table->foreignId('order_id')
                ->nullable()
                ->after('bonus_card_id')
                ->constrained('orders')
                ->onDelete('set null');

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_bonus_history', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};


