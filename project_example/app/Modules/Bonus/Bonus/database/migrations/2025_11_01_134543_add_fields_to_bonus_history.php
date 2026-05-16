<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_bonus_history', function (Blueprint $table) {
            $table->boolean('is_manual')->default(false)->after('status');
            $table->string('reason')->nullable()->after('is_manual');

            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('moonshine_users')
                ->onDelete('set null')
                ->after('reason');

            $table->text('comment')->nullable()->after('moonshine_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_bonus_history', function (Blueprint $table) {
            $table->dropColumn(['is_manual', 'reason', 'moonshine_user_id', 'comment']);
        });
    }
};
