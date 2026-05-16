<?php

namespace YandexFeed\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YandexFeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'file_name',
        'domain_name',
        'company_name',
        'selected_categories',
        'export_only_available',
        'export_only_with_stock',
        'use_https',
        'add_referrer',
        'encoding',
        'step_time',
        'currencies',
        'offer_type',
        'cron_interval_hours',
        'cron_start_time',
        'cron_php_path',
        'cron_auto_install',
        'is_active',
    ];

    protected $casts = [
        'selected_categories' => 'array',
        'currencies' => 'array',
        'export_only_available' => 'boolean',
        'export_only_with_stock' => 'boolean',
        'use_https' => 'boolean',
        'add_referrer' => 'boolean',
        'cron_auto_install' => 'boolean',
        'is_active' => 'boolean',
        'step_time' => 'integer',
        'cron_interval_hours' => 'integer',
    ];

    /**
     * Получить URL фида
     */
    public function getFeedUrl(): string
    {
        $protocol = $this->use_https ? 'https' : 'http';
        $domain = $this->domain_name ?? config('app.url');
        $domain = str_replace(['http://', 'https://'], '', $domain);
        
        return "{$protocol}://{$domain}/feeds/yandex/{$this->file_name}";
    }

    /**
     * Получить путь к файлу фида
     */
    public function getFeedPath(): string
    {
        return storage_path("app/feeds/yandex/{$this->file_name}");
    }

    /**
     * Получить настройки валют по умолчанию
     */
    public static function getDefaultCurrencies(): array
    {
        return [
            ['id' => 'RUB', 'rate' => '1'],
            ['id' => 'USD', 'rate' => '68.79'],
            ['id' => 'EUR', 'rate' => '78.32'],
        ];
    }
}

