<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebook_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gradebook_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('student_name');
            $table->string('group_name')->nullable();
            $table->string('semester', 50)->nullable();
            $table->decimal('module1_score', 6, 2)->default(0);
            $table->decimal('module2_score', 6, 2)->default(0);
            $table->decimal('total_score', 6, 2)->default(0);
            $table->string('final_grade', 50)->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();

            $table->index(['gradebook_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebook_rows');
    }
};
