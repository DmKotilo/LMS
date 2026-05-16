<?php

namespace App\Services;

use Illuminate\Http\Request;
use Product\Models\Product;

class ProductSchemaService
{
    /**
     * Генерация Schema.org разметки для продукта
     *
     * @param Product $product
     * @return array|null
     */
    public function generate(Product $product): ?array
    {
        // Загружаем необходимые связи если не загружены
        if (!$product->relationLoaded('images')) {
            $product->load(['images', 'brands', 'categories', 'manufacturer', 'reviews', 'attributes_values.attribute']);
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => html_entity_decode($product->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ];

        // Description (описание)
        if ($product->description) {
            $schema['description'] = strip_tags($product->description);
        }

        // SKU / артикул
        if ($product->article_number) {
            $schema['sku'] = (string) $product->article_number;
        }

        // Image (изображения) - все изображения продукта
        $images = $product->images;
        if ($images && $images->count() > 0) {
            $imageUrls = $images->map(function($image) {
                return url('/storage/' . $image->path);
            })->toArray();
            
            $schema['image'] = count($imageUrls) === 1 ? $imageUrls[0] : $imageUrls;
        }

        // Brand (бренд)
        if ($product->brands && $product->brands->count() > 0) {
            $brand = $product->brands->first();
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $brand->name,
            ];
        }

        // Manufacturer (производитель)
        if ($product->manufacturer) {
            $schema['manufacturer'] = [
                '@type' => 'Organization',
                'name' => $product->manufacturer->name,
            ];
        }

        // Category (категория) - вся цепочка через >
        if ($product->categories && $product->categories->count() > 0) {
            $categoryNames = $product->categories
                ->sortBy('lvl')
                ->pluck('name')
                ->toArray();
            
            $schema['category'] = implode(' > ', $categoryNames);
        }

        // Weight (вес) - как QuantitativeValue
        if ($product->weight) {
            $schema['weight'] = [
                '@type' => 'QuantitativeValue',
                'value' => (float) $product->weight,
                'unitCode' => $product->weight_type?->value === 'kg' ? 'KGM' : 'GRM',
            ];
        }

        // AdditionalProperty (дополнительные атрибуты)
        if ($product->attributes_values && $product->attributes_values->count() > 0) {
            $additionalProperties = [];
            
            foreach ($product->attributes_values as $attrValue) {
                if ($attrValue->value && $attrValue->attribute) {
                    $additionalProperties[] = [
                        '@type' => 'PropertyValue',
                        'name' => $attrValue->attribute->name,
                        'value' => $attrValue->value,
                    ];
                }
            }
            
            if (count($additionalProperties) > 0) {
                $schema['additionalProperty'] = $additionalProperties;
            }
        }

        // Offers (предложение с ценой)
        $currentPrice = $product->sale_price ?? $product->total_price ?? $product->price;
        
        $offerData = [
            '@type' => 'Offer',
            'url' => route('catalog.product.show', $product->slug),
            'priceCurrency' => 'RUB',
            'price' => (float) $currentPrice,
            'priceValidUntil' => now()->addDays(30)->format('Y-m-d'),
            'itemCondition' => 'https://schema.org/NewCondition',
        ];

        // Availability (доступность) - более детальная логика
        $quantity = $product->quantity ?? 0;
        if ($quantity > 10) {
            $offerData['availability'] = 'https://schema.org/InStock';
        } elseif ($quantity > 0) {
            $offerData['availability'] = 'https://schema.org/LimitedAvailability';
        } else {
            $offerData['availability'] = 'https://schema.org/OutOfStock';
        }

        $schema['offers'] = $offerData;

        // AggregateRating (средний рейтинг из отзывов)
        $approvedReviews = $product->reviews()
            ->where('status', \App\Modules\Review\src\Enums\ReviewStatusEnum::Approved)
            ->get();
        
        if ($approvedReviews->count() > 0) {
            $averageRating = $approvedReviews->avg('rating');
            
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($averageRating, 1),
                'reviewCount' => $approvedReviews->count(),
                'bestRating' => 5,
                'worstRating' => 1,
            ];
            
            // Review (отдельные отзывы) - берем топ 10
            $reviews = [];
            foreach ($approvedReviews->take(10) as $review) {
                $reviewData = [
                    '@type' => 'Review',
                    'reviewRating' => [
                        '@type' => 'Rating',
                        'ratingValue' => $review->rating,
                        'bestRating' => 5,
                        'worstRating' => 1,
                    ],
                    'author' => [
                        '@type' => 'Person',
                        'name' => $review->user?->name ?? 'Анонимный пользователь',
                    ],
                ];
                
                if ($review->created_at) {
                    $reviewData['datePublished'] = $review->created_at->format('Y-m-d');
                }
                
                if ($review->comment) {
                    $reviewData['reviewBody'] = $review->comment;
                }
                
                $reviews[] = $reviewData;
            }
            
            if (count($reviews) > 0) {
                $schema['review'] = $reviews;
            }
        }

        return $schema;
    }

    /**
     * Генерация OpenGraph и SEO meta тегов для продукта
     *
     * @param Product $product
     * @return array
     */
    public function generateMetaTags(Product $product): array
    {
        // Загружаем изображения если не загружены
        if (!$product->relationLoaded('images')) {
            $product->load('images');
        }

        $title = $product->seo_title ?? $product->name;
        $description = $product->seo_description ?? $product->short_description ?? strip_tags($product->description);
        $keywords = $product->keywords ?? null;

        $metaTags = [
            // Обычные SEO теги
            'title' => $title,
            'description' => $description,
            
            // OpenGraph теги
            'og:type' => 'product',
            'og:site_name' => config('app.name', 'PrimeFoods'),
            'og:title' => $title,
            'og:description' => $description,
            'og:url' => route('catalog.product.show', $product->slug),
            'og:locale' => 'ru_RU',
        ];

        // Добавляем keywords если есть
        if ($keywords) {
            $metaTags['keywords'] = $keywords;
        }

        // Добавляем изображение если есть
        $firstImage = $product->images->first();
        if ($firstImage) {
            $imageUrl = url('/storage/' . $firstImage->path);
            $metaTags['og:image'] = $imageUrl;
            $metaTags['og:image:secure_url'] = $imageUrl;
            $metaTags['og:image:alt'] = $description;
        }

        return $metaTags;
    }

    /**
     * Проверить, находимся ли мы на странице продукта
     *
     * @param Request $request
     * @return bool
     */
    public function isProductPage(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        return $routeName === 'catalog.product.show';
    }

    /**
     * Получить продукт из текущего маршрута
     *
     * @param Request $request
     * @return Product|null
     */
    public function getProductFromRoute(Request $request): ?Product
    {
        if (!$this->isProductPage($request)) {
            return null;
        }

        $product = $request->route('product');
        
        if ($product instanceof Product) {
            return $product;
        }

        return null;
    }
}

