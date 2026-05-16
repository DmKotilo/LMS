<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Изменяем тип поля active_date с date на datetime
        // PostgreSQL
        DB::statement('ALTER TABLE user_bonus_history ALTER COLUMN active_date TYPE timestamp USING active_date::timestamp');
    }

    public function down(): void
    {
        // Возвращаем обратно к date
        DB::statement('ALTER TABLE user_bonus_history ALTER COLUMN active_date TYPE date USING active_date::date');
    }
};

