<?php

namespace Article\Database\Seeders;
use App\Modules\Blog\Article\src\Models\ArticleRelated;
use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use Article\Models\Article;
use Article\Models\ArticleSelection;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;



class ArticleSeeder extends Seeder
{
    protected function attachImagesToArticleSelection(ArticleSelection $articleSelection, int $id): void
    {
        $sourcePath = public_path("images/Seeder/ArticleSelection/{$id}/image/");

        if (!file_exists($sourcePath)) {

            $id = rand(1, 4);
            $sourcePath = public_path("images/Seeder/ArticleSelection/{$id}/image/");
        }

        $files = glob("{$sourcePath}{$id}*.jpg");

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $storagePath = "ArticleSelection/{$articleSelection->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($filePath));

            File::create([
                'fileable_id' => $articleSelection->id,
                'fileable_type' => ArticleSelection::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'image',
                'extension' => 'jpg',
                'size' => filesize($filePath),
                'disk' => 'public',
            ]);

        }

    }

    protected function attachImagesToArticle(Article $article, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Article/{$id}/image/");
        if (!file_exists($sourcePath)) {

            $id = rand(1, 4);
            $sourcePath = public_path("images/Seeder/ArticleSelection/{$id}/image/");
        }

        $files = glob("{$sourcePath}{$id}*.*");

        foreach ($files as $filePath) {
            $filename = basename($filePath);
            $storagePath = "Article/{$article->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($filePath));

            File::create([
                'fileable_id' => $article->id,
                'fileable_type' => Article::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'image',
                'extension' => 'jpg',
                'size' => filesize($filePath),
                'disk' => 'public',
            ]);

        }

    }

    public function run(): void
    {
        if (Storage::disk('public')->exists('ArticleSelection')) {
            Storage::disk('public')->deleteDirectory('ArticleSelection');
        }
        $selectionsData = [
            [
                'title' => 'О компании',
                'description' => 'Новости и материалы о нашей компании',
                'order_number' => 1,
                'is_displayed' => true,
                'views_count' => 7,
                'is_popular' => rand(0,1),
                'is_main' => true,
            ],
            [
                'title' => 'Новости',
                'description' => 'Актуальные события и обновления',
                'order_number' => 2,
                'is_displayed' => true,
                'views_count' => 0,
                'is_popular' => rand(0,1),
            ],
            [
                'title' => 'Об отрубе',
                'description' => 'Полезная информация о продуктах с отрубями',
                'order_number' => 3,
                'is_displayed' => true,
                'views_count' => 10,
                'is_popular' => rand(0,1),
            ],
            [
                'title' => 'О стейке',
                'description' => 'Советы и рецепты приготовления стейков',
                'order_number' => 4,
                'is_displayed' => true,
                'views_count' =>230,
                'is_popular' => rand(0,1),
            ],
            [
                'title' => 'Мы в СМИ',
                'description' => 'Публикации о нас в различных медиа',
                'order_number' => 5,
                'is_displayed' => true,
                'views_count' => 43,
                'is_popular' => rand(0,1),
            ],
            [
                'title' => 'Фермерские продукты',
                'description' => 'Информация о наших фермерских поставках',
                'order_number' => 6,
                'is_displayed' => true,
                'views_count' => 50,
                'is_popular' => rand(0,1),
            ],
        ];
        foreach ($selectionsData as $data) {
            $articleSelection = ArticleSelection::firstOrCreate(
                ['title' => $data['title'],],
                $data,

            );
            $this->attachImagesToArticleSelection($articleSelection, $articleSelection->id);

        }
        ///
        ///
        ///
        $articlesBySelection = [
            'О компании' => [
                [
                    'title' => 'Поперёк и точка: Почему важно нарезать мясо поперёк волокон?',
                    'description' => 'Расскажем, как правильно определить волокна и почему это так важно.',
                    'mini_description' => 'Расскажем, как правильно определить волокна и почему это так важно.',
                    'text' => 'Подробный разбор способов нарезки мяса, которые сохраняют его сочность и мягкость.',
                    'tags' => 'Статья, Разделка, Мясо, Советы',
                    'author' => 'Иван Иванов',
                ],
                [
                    'title' => 'Фермерская утка PRIME FOODS: эталон качества для вашего стола',
                    'description' => 'В мире гастрономии ингредиенты играют ключевую роль. PRIME FOODS задает новые стандарты.',
                    'mini_description' => 'В мире гастрономии ингредиенты играют ключевую роль. PRIME FOODS задает новые стандарты.',
                    'text' => 'История фермерской утки, её польза и лучшие способы приготовления.',
                    'tags' => 'Статья, Утка, PRIMEFOODS, Качество',
                    'author' => 'Мария Петрова',
                ],
                [
                    'title' => 'Не Рибаем единым',
                    'description' => 'Какие ещё отрубы достойны внимания гурманов.',
                    'mini_description' => 'Какие ещё отрубы достойны внимания гурманов.',
                    'text' => 'Обзор популярных и редких стейков, которые стоит попробовать.',
                    'tags' => 'Статья, Отрубы, Стейк, Гурманы',
                    'author' => 'Алексей Сидоров',
                ],
            ],
            'Новости' => [
                [
                    'title' => 'PRIME FOODS открывает новый фермерский цех',
                    'description' => 'Расширяем производство и улучшаем качество продукции.',
                    'mini_description' => 'Расширяем производство и улучшаем качество продукции.',
                    'text' => 'Официальное открытие нового цеха — важный шаг для развития компании и повышения качества поставок.',
                    'tags' => 'Статья, Производство, PRIMEFOODS, Новости',
                    'author' => 'Елена Кузнецова',
                ],
                [
                    'title' => 'Наши продукты вошли в топ лучших на выставке',
                    'description' => 'PRIME FOODS получила награду за высокое качество.',
                    'mini_description' => 'PRIME FOODS получила награду за высокое качество.',
                    'text' => 'На международной выставке компания получила признание экспертов и посетителей.',
                    'tags' => 'Статья, Выставка, Награда, Качество',
                    'author' => 'Дмитрий Орлов',
                ],
            ],
            'Об отрубе' => [
                [
                    'title' => 'Отрубы: чем они полезны и как использовать',
                    'description' => 'Рассказываем о пользе отрубей в рационе.',
                    'mini_description' => 'Рассказываем о пользе отрубей в рационе.',
                    'text' => 'Отруби содержат витамины группы B, клетчатку и микроэлементы. Их можно добавлять в выпечку и блюда для здоровья.',
                    'tags' => 'Статья, Отруби, Здоровье, Питание',
                    'author' => 'Ольга Фёдорова',
                ],
                [
                    'title' => 'Рецепты с отрубями: вкусно и полезно',
                    'description' => 'Несколько простых идей для домашнего приготовления.',
                    'mini_description' => 'Несколько простых идей для домашнего приготовления.',
                    'text' => 'Блины, каши, запеканки — как правильно добавлять отруби в еду, чтобы сохранить вкус и пользу.',
                    'tags' => 'Статья, Отруби, Рецепты, Польза',
                    'author' => 'Сергей Михайлов',
                ],
            ],
            'О стейке' => [
                [
                    'title' => 'Топ-5 стейков для гриля',
                    'description' => 'Какие стейки лучше всего подходят для жарки на открытом огне.',
                    'mini_description' => 'Какие стейки лучше всего подходят для жарки на открытом огне.',
                    'text' => 'Рибай, Нью-Йорк, Тибон и другие виды стейков — обзор с рекомендациями по прожарке.',
                    'tags' => 'Статья, Стейк, Гриль, Мясо',
                    'author' => 'Никита Волков',
                ],
                [
                    'title' => 'Секреты идеальной прожарки стейка',
                    'description' => 'Как добиться сочности и аромата.',
                    'mini_description' => 'Как добиться сочности и аромата.',
                    'text' => 'От выбора мяса до времени приготовления — пошаговый гид для начинающих и опытных гурманов.',
                    'tags' => 'Статья, Стейк, Прожарка, Советы',
                    'author' => 'Анастасия Лебедева',
                ],
            ],
            'Мы в СМИ' => [
                [
                    'title' => 'PRIME FOODS в журнале «Гастроном»',
                    'description' => 'Интервью с основателем компании.',
                    'mini_description' => 'Интервью с основателем компании.',
                    'text' => 'О том, как начинался бизнес и каких успехов удалось достичь.',
                    'tags' => 'Статья, СМИ, PRIMEFOODS, Интервью',
                    'author' => 'Игорь Ковалёв',
                ],
                [
                    'title' => 'О нас пишут федеральные СМИ',
                    'description' => 'PRIME FOODS снова в центре внимания.',
                    'mini_description' => 'PRIME FOODS снова в центре внимания.',
                    'text' => 'Наш подход к качеству и инновациям отмечен ведущими изданиями страны.',
                    'tags' => 'Статья, СМИ, PRIMEFOODS, Новости',
                    'author' => 'Татьяна Романова',
                ],
            ],
            'Фермерские продукты' => [
                [
                    'title' => 'Фермерская продукция: в чём её ценность?',
                    'description' => 'Почему натуральные продукты лучше для здоровья.',
                    'mini_description' => 'Почему натуральные продукты лучше для здоровья.',
                    'text' => 'Сравнение фермерской и промышленной продукции. Преимущества натурального подхода.',
                    'tags' => 'Статья, Фермерские, Продукты, Здоровье',
                    'author' => 'Владимир Ефимов',
                ],
                [
                    'title' => 'Наши фермеры: люди, которым можно доверять',
                    'description' => 'Кто стоит за PRIME FOODS.',
                    'mini_description' => 'Кто стоит за PRIME FOODS.',
                    'text' => 'Истории фермеров, их подход к выращиванию и забота о каждом продукте.',
                    'tags' => 'Статья, Фермеры, PRIMEFOODS, Доверие',
                    'author' => 'Марина Белова',
                ],
            ],
        ];




        $selections = ArticleSelection::where('is_displayed', true)->get();

        $createdArticles = [];

        foreach ($selections as $selection) {
            $articles = $articlesBySelection[$selection->title] ?? [];

            foreach ($articles as $index => $data) {
                $article = Article::create(array_merge($data, [
                    'is_popular' => rand(0,1),
                    'publish'   => true,
                ]));

                $this->attachImagesToArticle($article, $article->id);

                $article->selections()->attach($selection->id, [
                    'order_number' => $index + 1,
                    'is_displayed' => true,
                ]);

                $createdArticles[] = $article;
            }
        }

        foreach ($createdArticles as $article) {
            $otherArticles = collect($createdArticles)->where('id', '!=', $article->id)->pluck('id');

            $relatedIds = $otherArticles->count() > 4
                ? $otherArticles->random(4)
                : $otherArticles;

            foreach ($relatedIds as $relatedId) {
                ArticleRelated::create([
                    'article_id'        => $article->id,
                    'related_article_id'=> $relatedId,
                ]);
            }
        }


    }
}
