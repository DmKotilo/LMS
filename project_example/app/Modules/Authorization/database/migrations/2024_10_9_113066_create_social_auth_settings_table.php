<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('social_auth_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'vk', 'yandex'
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        DB::table('social_auth_settings')->insert([
            ['name' => 'vk', 'is_available' => true],
            ['name' => 'yandex', 'is_available' => true],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_auth_settings');
    }
};
