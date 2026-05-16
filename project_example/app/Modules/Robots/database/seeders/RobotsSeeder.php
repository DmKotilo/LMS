<?php

namespace Robots\Database\Seeders;

use Illuminate\Database\Seeder;
use Robots\Models\Robot;

class RobotsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Создаем единственную запись robots.txt если её нет
        if (Robot::count() === 0) {
            $robot = Robot::create([
                'name' => 'robots.txt',
                'content' => $this->getDefaultRobotsContent(),
            ]);
            $robot->saveToFile();
        }
    }

    /**
     * Получить содержимое по умолчанию для robots.txt
     */
    private function getDefaultRobotsContent(): string
    {
        return <<<'EOT'
User-agent: *
Disallow: /admin
Disallow: /api
Disallow: /cart
Disallow: /checkout
Disallow: /login
Disallow: /register
Disallow: /password

# Разрешить доступ к статическим ресурсам
Allow: /css/
Allow: /js/
Allow: /images/
Allow: /fonts/

# Карта сайта
Sitemap: https://example.com/sitemap.xml
EOT;
    }

}

