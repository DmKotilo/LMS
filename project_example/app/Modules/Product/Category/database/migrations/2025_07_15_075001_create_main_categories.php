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
        Schema::create('main_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('description')->nullable();
            $table->string('text_color')->nullable();
            $table->string('desktop_font_size')->nullable();
            $table->string('mobile_font_size')->nullable();
            $table->string('desktop_line_height')->nullable();
            $table->string('mobile_line_height')->nullable();
            $table->string('desktop_photo');
            $table->string('mobile_photo');
            $table->boolean('is_active')->default(false);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_categories');
    }
};
