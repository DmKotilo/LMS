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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('comment_notifications')->default(false);
            $table->boolean('sale_notifications')->default(false);
            $table->boolean('email_notifications')->default(false);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('messenger_notifications')->default(false);
            $table->boolean('favorite_categories')->default(false);
            $table->string('often_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
