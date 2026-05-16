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
        Schema::create('recipe_variantables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_variant_id')->constrained()->cascadeOnDelete();
            $table->morphs('recipe_variantable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_variantables');
    }
};
