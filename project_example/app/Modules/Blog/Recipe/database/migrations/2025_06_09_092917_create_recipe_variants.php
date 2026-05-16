<?php

use App\Modules\Blog\Recipe\src\Enums\RecipeVariantEnum;
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
        Schema::create('recipe_variants', function (Blueprint $table) {
            $table->id();
            $table->enum(
                'name',
                array_column(RecipeVariantEnum::cases(), 'value')
            );
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_variants');
    }
};
