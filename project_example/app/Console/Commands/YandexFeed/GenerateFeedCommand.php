<?php

namespace App\Console\Commands\YandexFeed;

use Illuminate\Console\Command;
use YandexFeed\Models\YandexFeed;
use YandexFeed\Services\YmlGeneratorService;

class GenerateFeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yandex-feed:generate {id? : ID фида для генерации}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация Яндекс фида';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = $this->argument('id');

        if ($id) {
            // Генерация конкретного фида
            $feed = YandexFeed::find($id);
            if (!$feed) {
                $this->error("Фид с ID {$id} не найден");
                return Command::FAILURE;
            }

            $this->generateFeed($feed);
        } else {
            // Генерация всех активных фидов
            $feeds = YandexFeed::where('is_active', true)->get();
            
            if ($feeds->isEmpty()) {
                $this->info('Нет активных фидов для генерации');
                return Command::SUCCESS;
            }

            foreach ($feeds as $feed) {
                $this->generateFeed($feed);
            }
        }

        return Command::SUCCESS;
    }

    protected function generateFeed(YandexFeed $feed): void
    {
        $this->info("Генерация фида: {$feed->name}");

        try {
            $generator = new YmlGeneratorService($feed);
            $success = $generator->saveToFile();

            if ($success) {
                $this->info("✓ Фид успешно сгенерирован: {$feed->getFeedUrl()}");
            } else {
                $this->error("✗ Ошибка при генерации фида: {$feed->name}");
            }
        } catch (\Exception $e) {
            $this->error("✗ Ошибка при генерации фида: {$e->getMessage()}");
        }
    }
}


