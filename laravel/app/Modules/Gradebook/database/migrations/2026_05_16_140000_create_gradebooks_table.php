<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gradebooks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('discipline')->nullable();
            $table->string('group_name')->nullable();
            $table->string('semester', 50)->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_filename')->nullable();
            $table->string('storage_path')->nullable();
            $table->timestamps();

            $table->index(['discipline', 'group_name', 'semester']);
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebooks');
    }
};
