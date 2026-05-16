<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Robots\Database\Seeders\RobotsSeeder;
use Robots\Models\Robot;

class GenerateRobots extends Command
{
    protected $signature = 'robots:generate';

    protected $description = 'Генерирует robots.txt из БД (восстанавливает после деплоя)';

    public function handle(): int
    {
        $robot = Robot::first();

        if (! $robot) {
            $this->info('Запись robots.txt не найдена, создаю через сидер...');
            app(RobotsSeeder::class)->run();
            $this->info('robots.txt успешно создан');
            return Command::SUCCESS;
        }

        if ($robot->saveToFile()) {
            $this->info('robots.txt успешно сгенерирован: ' . public_path('robots.txt'));
            return Command::SUCCESS;
        }

        $this->error('Не удалось записать robots.txt');
        return Command::FAILURE;
    }
}
