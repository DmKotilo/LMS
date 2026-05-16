<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('combos', function (Blueprint $table) {
            $table->text('keywords')->nullable();
        });
    }

    public function down()
    {
        Schema::table('combos', function (Blueprint $table) {
            $table->dropColumn('keywords'); // Удаляем новое поле
        });
    }
};
