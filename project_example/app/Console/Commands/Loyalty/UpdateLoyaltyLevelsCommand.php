<?php

namespace App\Console\Commands\Loyalty;

use Illuminate\Console\Command;
use Loyalty\Services\LoyaltyService;
use User\Models\User;

class UpdateLoyaltyLevelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loyalty:update-levels';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление уровней лояльности всех пользователей';

    /**
     * Execute the console command.
     */
    public function handle(LoyaltyService $loyaltyService): int
    {
        $this->info('Начало обновления уровней лояльности...');

        $users = User::whereHas('bonusHistories')->get();
        $updatedCount = 0;

        foreach ($users as $user) {
            $oldLevel = $user->loyaltyLevel?->loyaltyLevel?->name;
            
            $loyaltyService->updateUserLoyaltyLevel($user);
            
            $newLevel = $user->fresh()->loyaltyLevel?->loyaltyLevel?->name;
            
            if ($oldLevel !== $newLevel) {
                $updatedCount++;
                $this->info("User {$user->id}: {$oldLevel} → {$newLevel}");
            }
        }

        $this->info("Обновлено уровней: {$updatedCount} из {$users->count()}");

        return Command::SUCCESS;
    }
}

