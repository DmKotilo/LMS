<?php

namespace Comment\Database\Seeders; // Адаптируйте namespace под вашу структуру

use Comment\Enums\CommentStatusEnum;
use Comment\Models\Comment;
use Illuminate\Database\Seeder;
use Article\Models\Article;
use Illuminate\Support\Facades\DB;
class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articleIds = Article::limit(5)->pluck('id')->toArray();

        if (empty($articleIds)) {
//            echo "Нет статей для добавления комментариев.\n";
            return;
        }

        $userIds = [1, 2];

        if (empty($userIds)) {
//            echo "Нет пользователей для создания комментариев.\n";
            return;
        }

        // Примеры текстов комментариев
        $commentTexts = [
            'Отличная статья! Спасибо за полезную информацию.',
            'Очень познавательно. Расскажите больше о теме.',
            'Спасибо, помогло разобраться.',
            'Интересный взгляд на проблему.',
            'Не согласен с некоторыми моментами, но в целом хорошо.',
            'А как быть в такой-то ситуации?',
            'Спасибо автору за труд!',
            'Нужно попробовать описанный способ.',
            'Статья заставила задуматься.',
            'Кратко и по делу. Рекомендую.',
            'Добавьте, пожалуйста, примеры.',
            'Очень актуальная тема.',
        ];

        // Количество комментариев для генерации
        $numberOfComments = 20;

        for ($i = 0; $i < $numberOfComments; $i++) {
            $randomArticleId = $articleIds[array_rand($articleIds)];

            $randomUserId = $userIds[array_rand($userIds)];

            $randomText = $commentTexts[array_rand($commentTexts)];

            $statuses = [CommentStatusEnum::Approved, CommentStatusEnum::Approved, CommentStatusEnum::Pending]; // Повышаем шанс Approved
            $randomStatus = $statuses[array_rand($statuses)];

            try {
                Comment::create([
                    'user_id'   => $randomUserId,
                    'item_type' => Article::class, // Комментарий к статье
                    'item_id'   => $randomArticleId,
                    'text'      => $randomText,
                    'status'    => $randomStatus, // Используем случайный статус
                ]);
//                echo "Создан комментарий для статьи ID {$randomArticleId} от пользователя ID {$randomUserId}\n";
            } catch (\Exception $e) {
//                echo "Ошибка при создании комментария: " . $e->getMessage() . "\n";
            }
        }

    }
}
