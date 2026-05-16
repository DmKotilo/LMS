<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->text('keywords')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
        });
    }

    public function down()
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('keywords');
            $table->dropColumn('seo_title');
            $table->dropColumn('seo_description');
        });
    }
};
