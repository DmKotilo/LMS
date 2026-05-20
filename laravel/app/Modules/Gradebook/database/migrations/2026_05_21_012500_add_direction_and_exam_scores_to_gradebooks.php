<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gradebooks', function (Blueprint $table) {
            $table->string('direction_code', 50)->nullable()->after('discipline');
        });

        Schema::table('gradebook_rows', function (Blueprint $table) {
            $table->decimal('exam_score', 6, 2)->default(0)->after('module2_score');
        });
    }

    public function down(): void
    {
        Schema::table('gradebook_rows', function (Blueprint $table) {
            $table->dropColumn('exam_score');
        });

        Schema::table('gradebooks', function (Blueprint $table) {
            $table->dropColumn('direction_code');
        });
    }
};
