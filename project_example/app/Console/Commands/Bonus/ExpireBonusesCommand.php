<?php

namespace App\Console\Commands\Bonus;

use Bonus\Services\BonusService;
use Illuminate\Console\Command;

class ExpireBonusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bonuses:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Истечение бонусов по истечении 90 дней';

    /**
     * Execute the console command.
     */
    public function handle(BonusService $bonusService): int
    {

        $expiredCount = $bonusService->expireBonuses();


        return Command::SUCCESS;
    }
}

