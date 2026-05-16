<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_connections', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_current');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_connections');
    }
};
