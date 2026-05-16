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
        Schema::create('whitelist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whitelist_id')->constrained('user_whitelists')->onDelete('cascade');
            $table->unsignedBigInteger('item_id');
            $table->string('item_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whitelist_items');
    }
};
