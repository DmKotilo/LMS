<?php

namespace App\Console\Commands;

use Database\Seeders\FakeCatalogSeeder;
use Illuminate\Console\Command;

class RunFakeCatalogSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:run-fake-catalog-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запуска фейкового сидера каталога';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('db:seed', [
            '--class' => FakeCatalogSeeder::class,
        ]);

    }
}
