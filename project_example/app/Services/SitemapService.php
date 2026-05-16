<?php

namespace App\Services;

use Article\Models\Article;
use Category\Models\Category;
use Combo\Models\Combo;
use Combination\Models\Combination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Pages\Models\Page;
use Product\Models\Product;
use Promotion\Models\Promotion;
use Recipe\Models\Recipe;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapService
{
    protected string $baseUrl;

    public function __construct()
    {
        // config() — чтобы при config:cache на проде был правильный URL (env() тогда недоступен)
        $this->baseUrl = rtrim((string) config('app.url', 'https://shop.primefoods.ru'), '/');
    }

    /**
     * Генерирует полный sitemap для всех типов контента
     */
    public function generate(): void
    {
        $sitemap = Sitemap::create();

        // Добавляем главную страницу
        $sitemap->add(Url::create($this->baseUrl)
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // Добавляем статические страницы
        $sitemap->add(Url::create($this->baseUrl . '/faq')
            ->setPriority(0.7)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY));

        $sitemap->add(Url::create($this->baseUrl . '/promotion')
            ->setPriority(0.8)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

        //  товары
        $this->addProducts($sitemap);

        //  категории
        $this->addCategories($sitemap);

        //  статьи
        $this->addArticles($sitemap);

        //  рецепты
        $this->addRecipes($sitemap);

        //  страницы
        $this->addPages($sitemap);

        //  комбо наборы
        $this->addCombos($sitemap);

        //  комбинации
        $this->addCombinations($sitemap);

        //  акции
        $this->addPromotions($sitemap);

        //  sitemap
        $path = public_path('sitemap.xml');
        if (!$this->writeSitemapToFile($sitemap, $path)) {
            return;
        }

        Log::info('Sitemap: полная генерация sitemap завершена', [
            'file' => $path,
            'total_urls' => count($sitemap->getTags()),
        ]);
    }

    /**
     * Обновляет sitemap для конкретной модели
     */
    public function updateItem(string $modelType, $model, bool $shouldInclude = true): void
    {
        try {
            // Проверяем, что модель существует
            if (!$model || !isset($model->id)) {
                Log::warning('Sitemap: модель не существует или не имеет ID', [
                    'model_type' => $modelType,
                ]);
                return;
            }

            $sitemapPath = public_path('sitemap.xml');

            // Если файл не существует, создаем новый
            if (!file_exists($sitemapPath)) {
                Log::info('Sitemap: файл не найден, выполняется полная генерация', [
                    'model_type' => $modelType,
                    'model_id' => $model->id ?? null,
                ]);
                $this->generate();
                return;
            }

            // Загружаем существующий sitemap из XML
            $sitemap = $this->loadSitemapFromFile($sitemapPath);

            $url = $this->getUrlForModel($modelType, $model);
            $fullUrl = $url ? $this->baseUrl . $url : null;

            if (!$fullUrl) {
                Log::warning('Sitemap: не удалось получить URL для модели', [
                    'model_type' => $modelType,
                    'model_id' => $model->id ?? null,
                ]);
                return;
            }

            // Удаляем старый URL если он существует (фильтруем массив tags)
            $tags = $sitemap->getTags();
            $filteredTags = array_filter($tags, function ($tag) use ($fullUrl) {
                if ($tag instanceof Url) {
                    return $tag->url !== $fullUrl;
                }
                return true;
            });

            // Создаем новый sitemap с отфильтрованными тегами
            $newSitemap = Sitemap::create();
            foreach ($filteredTags as $tag) {
                $newSitemap->add($tag);
            }

            // Добавляем новый URL если запись должна быть включена
            $action = 'removed';
            if ($shouldInclude) {
                $lastModified = $model->updated_at ?? now();
                $urlObj = $this->createUrl($url, $modelType, $lastModified);
                $newSitemap->add($urlObj);
                $action = 'added';
            }

            if (!$this->writeSitemapToFile($newSitemap, $sitemapPath)) {
                return;
            }
        } catch (\Exception $e) {
            Log::warning('Sitemap: ошибка инкрементального обновления (сохранение сущности не прервано)', [
                'model_type' => $modelType,
                'model_id' => $model->id ?? null,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Записывает sitemap в файл. При ошибке (например, нет прав) логирует и не бросает исключение.
     */
    protected function writeSitemapToFile(Sitemap $sitemap, string $path): bool
    {
        try {
            $sitemap->writeToFile($path);
            return true;
        } catch (\Throwable $e) {
            Log::warning('Sitemap: не удалось записать файл (проверьте права на public/sitemap.xml или каталог public)', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Загружает sitemap из XML файла
     */
    protected function loadSitemapFromFile(string $path): Sitemap
    {
        $sitemap = Sitemap::create();

        if (!file_exists($path)) {
            return $sitemap;
        }

        $xml = simplexml_load_file($path);
        if (!$xml) {
            return $sitemap;
        }

        foreach ($xml->url as $urlElement) {
            $loc = (string) $urlElement->loc;
            $url = Url::create($loc);

            if (isset($urlElement->priority)) {
                $url->setPriority((float) $urlElement->priority);
            }

            if (isset($urlElement->changefreq)) {
                $changefreq = (string) $urlElement->changefreq;
                $url->setChangeFrequency($changefreq);
            }

            if (isset($urlElement->lastmod)) {
                $lastmod = (string) $urlElement->lastmod;
                try {
                    $date = \Carbon\Carbon::parse($lastmod);
                    $url->setLastModificationDate($date);
                } catch (\Exception $e) {
                    // Игнорируем ошибки парсинга даты
                }
            }

            $sitemap->add($url);
        }

        return $sitemap;
    }

    /**
     * Получает модель и проверяет, должна ли она быть в sitemap
     */
    public function shouldIncludeModel(string $modelType, $model): bool
    {
        // Проверяем, что модель существует
        if (!$model || !isset($model->id)) {
            return false;
        }

        try {
            return match ($modelType) {
                'product' => !empty($model->slug) && method_exists($model, 'isActive') && $model->isActive(),
                'category' => !empty($model->slug),
                'article' => (bool) $model->publish,
                'recipe' => (bool) $model->publish,
                'page' => (bool) ($model->is_active && !empty($model->slug)),
                'combo' => method_exists($model, 'products') ? $model->products()->exists() : false,
                'combination' => (bool) $model->is_active,
                'promotion' => (bool) $model->is_active,
                default => false,
            };
        } catch (\Exception $e) {
            Log::warning('Sitemap: error checking if model should be included', [
                'model_type' => $modelType,
                'model_id' => $model->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Добавляет товары в sitemap.
     * Та же логика, что и на сайте: Product::active() и isActive().
     * — Товар: is_active = true, не аналог, есть price, есть хотя бы одна категория, есть slug.
     */
    protected function addProducts(Sitemap $sitemap): void
    {
        Product::query()
            ->active()
            ->whereNotNull('slug')
            ->chunk(100, function ($products) use ($sitemap) {
                foreach ($products as $product) {
                    $url = route('catalog.product.show', ['product' => $product->slug], false);
                    $sitemap->add($this->createUrl($url, 'product', $product->updated_at));
                }
            });
    }

    /**
     * Добавляет категории в sitemap
     */
    protected function addCategories(Sitemap $sitemap): void
    {
        Category::query()
            ->whereNotNull('slug')
            ->chunk(100, function ($categories) use ($sitemap) {
                foreach ($categories as $category) {
                    // Используем slug_path для поддержки вложенных категорий
                    $categoryPath = $category->slug_path ?? $category->slug;
                    $url = route('catalog.index', ['category' => $categoryPath], false);
                    $sitemap->add($this->createUrl($url, 'category', $category->updated_at));
                }
            });
    }

    /**
     * Добавляет статьи в sitemap
     */
    protected function addArticles(Sitemap $sitemap): void
    {
        Article::where('publish', true)
            ->chunk(100, function ($articles) use ($sitemap) {
                foreach ($articles as $article) {
                    $url = route('blog.article.show', ['article' => $article->id], false);
                    $sitemap->add($this->createUrl($url, 'article', $article->updated_at));
                }
            });
    }

    /**
     * Добавляет рецепты в sitemap
     */
    protected function addRecipes(Sitemap $sitemap): void
    {
        Recipe::where('publish', true)
            ->chunk(100, function ($recipes) use ($sitemap) {
                foreach ($recipes as $recipe) {
                    $url = route('recipe.show', ['recipe' => $recipe->id], false);
                    $sitemap->add($this->createUrl($url, 'recipe', $recipe->updated_at));
                }
            });
    }

    /**
     * Добавляет страницы в sitemap
     */
    protected function addPages(Sitemap $sitemap): void
    {
        Page::where('is_active', true)
            ->whereNotNull('slug')
            ->chunk(100, function ($pages) use ($sitemap) {
                foreach ($pages as $page) {
                    $url = route('page.page.show', ['slug' => $page->slug], false);
                    $sitemap->add($this->createUrl($url, 'page', $page->updated_at));
                }
            });
    }

    /**
     * Добавляет комбо наборы в sitemap
     */
    protected function addCombos(Sitemap $sitemap): void
    {
        Combo::query()
            ->hasProducts()
            ->chunk(100, function ($combos) use ($sitemap) {
                foreach ($combos as $combo) {
                    $url = route('combo.show', ['combo' => $combo->id], false);
                    $sitemap->add($this->createUrl($url, 'combo', $combo->updated_at));
                }
            });
    }

    /**
     * Добавляет комбинации в sitemap
     */
    protected function addCombinations(Sitemap $sitemap): void
    {
        Combination::where('is_active', true)
            ->chunk(100, function ($combinations) use ($sitemap) {
                foreach ($combinations as $combination) {
                    $url = route('combination.show', ['combination' => $combination->id], false);
                    $sitemap->add($this->createUrl($url, 'combination', $combination->updated_at));
                }
            });
    }

    /**
     * Добавляет акции в sitemap
     */
    protected function addPromotions(Sitemap $sitemap): void
    {
        Promotion::where('is_active', true)
            ->chunk(100, function ($promotions) use ($sitemap) {
                foreach ($promotions as $promotion) {
                    $url = route('promotion.show', ['promotion' => $promotion->id], false);
                    $sitemap->add($this->createUrl($url, 'promotion', $promotion->updated_at));
                }
            });
    }

    /**
     * Создает URL объект для sitemap
     */
    protected function createUrl(string $url, string $type, $lastModified = null): Url
    {
        $fullUrl = $this->baseUrl . $url;

        $urlObj = Url::create($fullUrl);

        // Устанавливаем приоритет в зависимости от типа контента
        $priority = match ($type) {
            'product' => 0.9,
            'category' => 0.8,
            'article' => 0.7,
            'recipe' => 0.7,
            'page' => 0.6,
            'combo' => 0.8,
            'combination' => 0.7,
            'promotion' => 0.8,
            default => 0.5,
        };
        $urlObj->setPriority($priority);

        // Устанавливаем частоту обновления
        $changeFreq = match ($type) {
            'product' => Url::CHANGE_FREQUENCY_DAILY,
            'category' => Url::CHANGE_FREQUENCY_WEEKLY,
            'article' => Url::CHANGE_FREQUENCY_WEEKLY,
            'recipe' => Url::CHANGE_FREQUENCY_WEEKLY,
            'page' => Url::CHANGE_FREQUENCY_MONTHLY,
            'combo' => Url::CHANGE_FREQUENCY_WEEKLY,
            'combination' => Url::CHANGE_FREQUENCY_MONTHLY,
            'promotion' => Url::CHANGE_FREQUENCY_WEEKLY,
            default => Url::CHANGE_FREQUENCY_MONTHLY,
        };
        $urlObj->setChangeFrequency($changeFreq);

        // Устанавливаем дату последнего изменения
        if ($lastModified) {
            $urlObj->setLastModificationDate($lastModified);
        }

        return $urlObj;
    }

    /**
     * Получает URL для модели по типу и модели
     */
    protected function getUrlForModel(string $modelType, $model): ?string
    {
        try {
            // Проверяем, что модель существует и имеет необходимые свойства
            if (!$model || !isset($model->id)) {
                return null;
            }

            return match ($modelType) {
                'product' => isset($model->slug) ? route('catalog.product.show', ['product' => $model->slug], false) : null,
                'category' => isset($model->slug) ? route('catalog.index', ['category' => ($model->slug_path ?? $model->slug)], false) : null,
                'article' => route('blog.article.show', ['article' => $model->id], false),
                'recipe' => route('recipe.show', ['recipe' => $model->id], false),
                'page' => isset($model->slug) ? route('page.page.show', ['slug' => $model->slug], false) : null,
                'combo' => route('combo.show', ['combo' => $model->id], false),
                'combination' => route('combination.show', ['combination' => $model->id], false),
                'promotion' => route('promotion.show', ['promotion' => $model->id], false),
                default => null,
            };
        } catch (\Exception $e) {
            // Если роут не найден или произошла ошибка, возвращаем null
            Log::warning('Sitemap: error getting URL for model', [
                'model_type' => $modelType,
                'model_id' => $model->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}

