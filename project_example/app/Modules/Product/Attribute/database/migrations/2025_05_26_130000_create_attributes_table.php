<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('input_type')->nullable(); //bool / text / select / number
            $table->string('slug')->unique();
            $table->boolean('is_default')->default(false); //неудаляемая характеристика
            $table->boolean('is_required')->default(false); //обязательно для заполнения
            $table->boolean('is_active')->default(true); //показывать при заполнении и редактировании продуктов
            $table->boolean('is_show_filter')->default(true); //показывать в фильтре

            $table->text('options')->nullable(); //варианты выбора при input_type == select

            $table->boolean('is_many_checked_options')->default(false); //возможность выбрать несколько вариантов выбора при input_type == select
            $table->boolean('is_select_writable')->default(false); //возможность выбрать несколько вариантов выбора при input_type == select

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
