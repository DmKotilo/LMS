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
        Schema::create('yandex_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Название профиля');
            $table->string('file_name')->comment('Имя файла фида');
            $table->string('domain_name')->nullable()->comment('Доменное имя');
            $table->string('company_name')->nullable()->comment('Название компании');
            
            // Настройки экспорта
            $table->json('selected_categories')->nullable()->comment('Выбранные категории для экспорта');
            $table->boolean('export_only_available')->default(true)->comment('Выгружать только доступные к покупке товары');
            $table->boolean('use_https')->default(true)->comment('Использовать HTTPS в ссылках');
            $table->boolean('add_referrer')->default(false)->comment('Добавлять реферер к ссылкам');
            $table->string('encoding', 20)->default('utf-8')->comment('Кодировка фида');
            $table->integer('step_time')->default(30)->comment('Время выполнения шага (секунды), 0 - выгрузить все сразу');
            
            // Настройки валют
            $table->json('currencies')->nullable()->comment('Настройки валют');
            
            // Настройки типа описания
            $table->string('offer_type', 50)->default('simple')->comment('Тип описания: simple, arbitrary, combined');
            
            // Настройки cron
            $table->integer('cron_interval_hours')->nullable()->comment('Интервал между запусками (часов)');
            $table->time('cron_start_time')->nullable()->comment('Время запуска');
            $table->string('cron_php_path')->nullable()->default('/usr/local/php/bin/php')->comment('Путь к PHP');
            $table->boolean('cron_auto_install')->default(false)->comment('Установить автоматически');
            $table->boolean('is_active')->default(true)->comment('Активен ли фид');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yandex_feeds');
    }
};

