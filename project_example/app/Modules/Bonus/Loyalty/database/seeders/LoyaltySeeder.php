<?php

namespace Loyalty\Database\Seeders;
use Illuminate\Database\Seeder;
use Loyalty\Models\LoyaltyLevel;

class LoyaltySeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'name' => 'Rare',
                'duration_days' => 90,
                'discount_percent' => 2,
                'coefficient' => 39999,
            ],
            [
                'name' => 'Medium',
                'duration_days' => 90,
                'discount_percent' => 3,
                'coefficient' => 69999,
            ],
            [
                'name' => 'Well Done',
                'duration_days' => 90,
                'discount_percent' => 5,
                'coefficient' => 70000,
            ],
        ];

        foreach ($levels as $level) {
            LoyaltyLevel::updateOrCreate(
                ['name' => $level['name']],
                $level
            );
        }
    }
}
