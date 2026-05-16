<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('uuid_1c')->nullable();
            $table->foreignId('parent_category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->text('explanation')->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('lvl')->nullable();
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
