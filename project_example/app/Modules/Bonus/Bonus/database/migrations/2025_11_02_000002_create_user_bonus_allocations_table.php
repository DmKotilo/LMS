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
        Schema::create('user_bonus_allocations', function (Blueprint $table) {
            $table->id();
            
            // ID записи списания (withdrawal)
            $table->foreignId('withdrawal_id')
                ->constrained('user_bonus_history')
                ->onDelete('cascade');
            
            // ID записи начисления (accrual), из которого списали
            $table->foreignId('accrual_id')
                ->constrained('user_bonus_history')
                ->onDelete('cascade');
            
        
            $table->decimal('amount', 10, 2);
            
            $table->timestamps();
            
            $table->index('withdrawal_id');
            $table->index('accrual_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bonus_allocations');
    }
};

