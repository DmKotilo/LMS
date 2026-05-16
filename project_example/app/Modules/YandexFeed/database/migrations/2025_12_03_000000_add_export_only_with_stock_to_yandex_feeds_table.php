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
        Schema::table('yandex_feeds', function (Blueprint $table) {
            $table->boolean('export_only_with_stock')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('yandex_feeds', function (Blueprint $table) {
            $table->dropColumn('export_only_with_stock');
        });
    }
};

