<?php

namespace Pages\Database\Seeders;


use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Pages\Models\Page;
use Pages\Models\Team;

class TeamSeeder extends Seeder
{
    public function run()
    {
        $teams = [
            [
                'full_name' => 'Андрей Ниценко',
                'position' => '1',
                'description' => 'Основатель Prime Foods',
            ],
            [
                'full_name' => 'Олег Васильев',
                'position' => '2',
                'description' => 'Директор по закупкам',
            ],
            [
                'full_name' => 'Елена Морозова',
                'position' => '3',
                'description' => 'Главный технолог',
            ],
            [
                'full_name' => 'Виктор Петров',
                'position' => '4',
                'description' => 'Руководитель логистики',
            ],
            [
                'full_name' => 'Наталья Кузнецова',
                'position' => '5',
                'description' => 'Шеф-повар',
            ],
            [
                'full_name' => 'Игорь Смирнов',
                'position' => '6',
                'description' => 'Директор по маркетингу',
            ],
        ];

        foreach ($teams as $index => $teamData) {
            $team = Team::create([
                'full_name' => $teamData['full_name'],
                'position' => $teamData['position'],
                'description' => $teamData['description'],
            ]);
            $this->attachImageToTeam($team, $index+1);

        }
    }

    protected function attachImageToTeam(Team $team, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Team/{$id}/image");

        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Team/{$team->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $team->id,
            'fileable_type' => Team::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'images',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }

}
