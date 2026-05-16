<?php

namespace App\Console\Commands;

use App\Services\SitemapService;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерирует полный sitemap.xml для всех типов контента';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Начинаю генерацию sitemap...');

        try {
            $sitemapService->generate();
            $this->info('Sitemap успешно сгенерирован: ' . public_path('sitemap.xml'));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Ошибка при генерации sitemap: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

