<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public $model = \Product\Models\Product::class;
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('uuid_bitrix24')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('uuid_bitrix24');
        });


    }
};
