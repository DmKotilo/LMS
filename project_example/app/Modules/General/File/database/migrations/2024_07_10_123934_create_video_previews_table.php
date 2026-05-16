<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('video_previews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained('files')->onDelete('cascade');
            $table->foreignId('preview_id')->constrained('files')->onDelete('cascade');
            $table->unique('video_id'); // у видео может быть только одно превью
        });
    }

    public function down()
    {
        Schema::dropIfExists('video_previews');
    }
};
