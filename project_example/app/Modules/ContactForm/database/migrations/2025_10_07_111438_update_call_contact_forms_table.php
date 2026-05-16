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
        Schema::table('call_contact_forms', function (Blueprint $table) {
            $table->string('time_interval')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('call_contact_forms', function (Blueprint $table) {
            $table->dropColumn(["time_interval"]);
            $table->dropColumn(["type"]);
            $table->dropColumn(["status"]);
        });
    }
};
