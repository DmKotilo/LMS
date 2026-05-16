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
        Schema::table('banners', function (Blueprint $table) {
            $table->unsignedBigInteger('global_promotion_id')->nullable();
            $table->string('location')->nullable();
            $table->boolean('only_for_new_client')->default(false);

            $table->foreign('global_promotion_id')
                ->references('id')
                ->on('global_promotions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['global_promotion_id']);
            $table->dropColumn(['global_promotion_id', 'location', 'only_for_new_client']);
        });
    }
};

