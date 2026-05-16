<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public $model = \User\Models\User::class;
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->nullable();
            $table->string('second_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->string('gender')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('phone', 20)->nullable()->unique();
            $table->string('new_phone', 20)->nullable()->unique();
            $table->string('phone_deleted', 20)->nullable();
            $table->string('phone_additional', 20)->nullable();
            $table->string('email', 100)->nullable()->unique();
            $table->string('new_email')->nullable();
            $table->string('email_deleted', 100)->nullable();
            $table->string('password', 255)->nullable();
            $table->boolean('is_self_deleted')->default(false);
            $table->date('email_verified_at')->nullable();
            $table->date('phone_verified_at')->nullable();
            $table->boolean('admin_verify')->default(false);
            $table->boolean('is_blocked')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
