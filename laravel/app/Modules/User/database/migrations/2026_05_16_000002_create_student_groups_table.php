<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('education_form', 32);
            $table->unsignedTinyInteger('course');
            $table->timestamps();

            $table->unique(['name', 'education_form', 'course']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_groups');
    }
};
