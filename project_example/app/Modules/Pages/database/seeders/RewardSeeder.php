<?php

namespace Pages\Database\Seeders;


use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Pages\Models\Page;
use Pages\Models\Reward;
use Pages\Models\Team;

class RewardSeeder extends Seeder
{
    public function run()
    {
        $rewards = [
            [
                'name' => 'Благодарственное письмо',
                'position' => '1',
            ],
            [
                'name' => 'Свидетельство 2020',
                'position' => '2',
            ],
            [
                'name' => 'Диплом',
                'position' => '3',
            ],
            [
                'name' => 'Благодарность',
                'position' => '4',
            ],
            [
                'name' => 'Благодарственное письмо',
                'position' => '5',
            ],
            [
                'name' => 'Свидетельство 2020',
                'position' => '6',
            ],
        ];

        foreach ($rewards as $index => $rewardData) {
            $reward = Reward::create([
                'name' => $rewardData['name'],
                'position' => $rewardData['position'],
            ]);
            $this->attachImageToReward($reward, $index+1);

        }
    }

    protected function attachImageToReward(Reward $reward, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Reward/{$id}/image");

        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Reward/{$reward->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $reward->id,
            'fileable_type' => Reward::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'images',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }

}
