<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DebugEnvCommand extends Command
{
    protected $signature = 'debug:env {action : on|off}';

    protected $description = 'Включить (on) или выключить (off) APP_DEBUG в .env и сбросить кэш конфига';

    public function handle(): int
    {
        $action = strtolower($this->argument('action'));
        if (!in_array($action, ['on', 'off'], true)) {
            $this->error('Укажите действие: on или off');
            return self::FAILURE;
        }

        $path = base_path('.env');
        if (!File::exists($path)) {
            $this->error('Файл .env не найден.');
            return self::FAILURE;
        }

        $content = File::get($path);
        $value = $action === 'on' ? 'true' : 'false';

        if (preg_match('/^APP_DEBUG=.*/m', $content)) {
            $newContent = preg_replace('/^APP_DEBUG=.*/m', 'APP_DEBUG=' . $value, $content);
        } else {
            $newContent = $content . "\nAPP_DEBUG=" . $value . "\n";
        }

        File::put($path, $newContent);
        $this->call('config:clear');

        $this->info('APP_DEBUG=' . $value . ', кэш конфига сброшен.');
        if ($action === 'on') {
            $this->warn('Не забудьте выполнить php artisan debug:env off после отладки!');
        }

        return self::SUCCESS;
    }
}
