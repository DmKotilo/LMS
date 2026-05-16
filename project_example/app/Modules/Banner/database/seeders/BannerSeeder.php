<?php

namespace Banner\Database\Seeders;
use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use Banner\Models\Banner;
use File\Models\Files\File;
use GlobalPromotion\Models\GlobalPromotion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BannerSeeder extends Seeder
{
    public function run()
    {
        if (Storage::disk('public')->exists('Banner')) {
            Storage::disk('public')->deleteDirectory('Banner');
        }

        // MainSlider (папки 1-3)
        $banner1 = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::MainSlider,
                'title' => 'Полезные подарки для стейк-энтузиастов',
            ],
            [
                'title' => 'Полезные подарки для стейк-энтузиастов',
                'description' => 'Все для вас как для себя',
                'link_url' => '#',
                'button_text' => 'Подробнее',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::MainSlider,
            ]
        );
        $this->attachImageToBanner($banner1, 1);

        $banner2 = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::MainSlider,
                'title' => 'PRIMEBEEF BOUTIQUE',
            ],
            [
                'title' => 'PRIMEBEEF BOUTIQUE',
                'description' => 'Гастрономическое искусство рядом с вами',
                'link_url' => '#',
                'button_text' => 'Подробнее',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::MainSlider,
            ]
        );
        $this->attachImageToBanner($banner2, 2);

        $banner3 = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::MainSlider,
                'title' => 'PRIMEBEEF',
            ],
            [
                'title' => 'PRIMEBEEF',
                'description' => 'Компания-поставщик лучших в своем классе продуктов FMCG',
                'link_url' => '#',
                'button_text' => 'Подробнее',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::MainSlider,
            ]
        );
        $this->attachImageToBanner($banner3, 3);

        // InfoBlock (папки 4-6)
        $infoTexts = [
            [
                'title' => 'Интернет магазин',
                'description' => 'Авторизуйтесь чтобы получить доступ к программе CASHBACK',
                'button_text' => 'Регистрация',
            ],
            [
                'title' => 'Для компаний',
                'description' => 'для HORECA',
                'button_text' => 'Подробнее',
            ],
            [
                'title' => 'Рецепты',
                'description' => 'Подскажем и покажем, что приготовить из наших продуктов',
                'button_text' => 'Подробнее',
            ],
        ];

        foreach (range(4, 6) as $i) {
            $index = $i - 4;
            $banner = Banner::firstOrCreate(
                [
                    'banner_type' => BannerTypeEnum::InfoBlock,
                    'title' => $infoTexts[$index]['title'],
                ],
                [
                    'title' => $infoTexts[$index]['title'],
                    'description' => $infoTexts[$index]['description'],
                    'link_url' => "#",
                    'button_text' => $infoTexts[$index]['button_text'],
                    'is_active' => true,
                    'banner_type' => BannerTypeEnum::InfoBlock,
                ]
            );
            $this->attachImageToBanner($banner, $i);
        }

        // Novelty (папка 7)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::Novelty,
                'title' => 'Сочные новинки для настоящих гурманов',
            ],
            [
                'addition_description' => 'Откройте мир мясных деликатесов',
                'title' => 'Сочные новинки для настоящих гурманов',
                'description' => 'Мы представляем уникальные сочетания традиционных рецептов и современных технологий, чтобы удовлетворить даже самых взыскательных гурманов.',
                'link_url' => "#",
                'button_text' => 'Все новинки',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::Novelty,
            ]
        );
        $this->attachImageToBanner($banner, 7);

        // ComboPacks (папка 8)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::ComboPacks,
                'title' => 'Пора на природу!',
            ],
            [
                'title' => 'Пора на природу!',
                'description' => 'Летние дни — это идеальное время для того, чтобы собраться с друзьями или семьей и насладиться атмосферой пикника. Что может быть лучше, чем ароматное мясо, жарящееся на гриле, и хорошая компания?',
                'addition_description' => 'Мы предлагаем вам уникальные комбо-наборы, которые сделают ваш отдых незабываемым!',
                'link_url' => "#",
                'button_text' => 'Подробнее',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::ComboPacks,
            ]
        );
        $this->attachImageToBanner($banner, 8);

        // деталка продукта (папки 9-11)
        $ProductDetailsTexts = [
            [
                'title' => 'Праздники с Prime Foods',
                'description' => 'Собрали все самое лучшее',
                'button_text' => 'Подробнее',
            ],
            [
                'title' => 'Праздники с Prime Foods',
                'description' => 'Собрали все самое лучшее',
                'button_text' => 'Подробнее',
            ],
            [
                'title' => 'Праздники с Prime Foods',
                'description' => 'Собрали все самое лучшее',
                'button_text' => 'Подробнее',
            ],
        ];

        foreach (range(9, 11) as $i) {
            $index = $i - 9;
            $banner = Banner::firstOrCreate(
                [
                    'banner_type' => BannerTypeEnum::ProductDetail,
                    'title' => $ProductDetailsTexts[$index]['title'],
                ],
                [
                    'title' => $ProductDetailsTexts[$index]['title'],
                    'description' => $ProductDetailsTexts[$index]['description'],
                    'link_url' => "#",
                    'button_text' => $ProductDetailsTexts[$index]['button_text'],
                    'is_active' => true,
                    'banner_type' => BannerTypeEnum::ProductDetail,
                ]
            );
            $this->attachImageToBanner($banner, $i);
        }

        // whiteListProduct (папка 12)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::ProductWhiteList,
                'title' => 'Сочные новинки',
            ],
            [
                'title' => 'Сочные новинки',
                'description' => 'Собрали все самое вкусное',
                'link_url' => "#",
                'button_text' => 'Подробнее',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::ProductWhiteList,
            ]
        );
        $this->attachImageToBanner($banner, 12);

        // whiteListRecipe (папка 13)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::RecipeWhiteList,
                'title' => 'Рецепты от Шеф-повара',
            ],
            [
                'title' => 'Рецепты от Шеф-повара',
                'description' => 'Более 2 000 рецептов',
                'link_url' => "#",
                'button_text' => 'Показать все',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::RecipeWhiteList,
            ]
        );
        $this->attachImageToBanner($banner, 13);

        // ArticleDetail (папка 14)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::ArticleDetail,
                'title' => 'Свежесть и качество мяса',
            ],
            [
                'title' => 'Свежесть и качество мяса',
                'description' => 'Мраморное мясо',
                'link_url' => "#",
                'button_text' => 'Купить',
                'is_active' => true,
                'banner_type' => BannerTypeEnum::ArticleDetail,
            ]
        );
        $this->attachImageToBanner($banner, 14);

        // PromotionPage1 (папка 15)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::PromotionPage,
                'title' => 'Полезные подарки для стейк-энтузиастов ',
            ],
            [
                'title' => 'Полезные подарки для стейк-энтузиастов ',
                'description' => 'Все для вас как для себя',
                'link_url' => "#",
                'button_text' => null,
                'is_active' => true,
                'banner_type' => BannerTypeEnum::PromotionPage,
            ]
        );
        $this->attachImageToBanner($banner, 15);

        // PromotionPage2 (папка 16)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::PromotionPage,
                'title' => 'Полезные подарки для стейк-энтузиастов ',
            ],
            [
                'title' => 'Полезные подарки для стейк-энтузиастов ',
                'description' => 'Все для вас как для себя',
                'link_url' => "#",
                'button_text' => null,
                'is_active' => true,
                'banner_type' => BannerTypeEnum::PromotionPage,
            ]
        );
        $this->attachImageToBanner($banner, 16);

        // PromotionPage3 (папка 17)
        $banner = Banner::firstOrCreate(
            [
                'banner_type' => BannerTypeEnum::PromotionPage,
                'title' => 'Полезные подарки для стейк-энтузиастов ',
            ],
            [
                'title' => 'Полезные подарки для стейк-энтузиастов ',
                'description' => 'Все для вас как для себя',
                'link_url' => "#",
                'button_text' => null,
                'is_active' => true,
                'banner_type' => BannerTypeEnum::PromotionPage,
            ]
        );
        $this->attachImageToBanner($banner, 17);

        // ComboDetail (папка 18) - Создаем 3 баннера
        $comboDetailTexts = [
            [
                'title' => 'Праздники с Prime Foods',
                'description' => 'Собрали все самое лучшее',
                'button_text' => 'Подробнее',
            ],
            [
                'title' => 'Праздники с Prime Foods',
                'description' => 'Собрали все самое лучшее',
                'button_text' => 'Подробнее',
            ],
            [
                'title' => 'Праздники с Prime Foods',
                'description' => 'Собрали все самое лучшее',
                'button_text' => 'Подробнее',
            ],
        ];

        foreach (range(18, 20) as $i) { // Используем папки 18, 19, 20
            $index = $i - 18;
            $banner = Banner::firstOrCreate(
                [
                    'banner_type' => BannerTypeEnum::ComboDetail,
                    'title' => $comboDetailTexts[$index]['title'],
                ],
                [
                    'title' => $comboDetailTexts[$index]['title'],
                    'description' => $comboDetailTexts[$index]['description'],
                    'link_url' => "#",
                    'button_text' => $comboDetailTexts[$index]['button_text'],
                    'is_active' => true,
                    'banner_type' => BannerTypeEnum::ComboDetail,
                ]
            );
            $this->attachImageToBanner($banner, $i); // Передаем номер папки (18, 19, 20)
        }

        // ProfilePage (папки 24-25)
        $profileBannerTexts = [
            [
                'title' => '+3000 баллов при заказе от 3 000 ₽',
                'description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'addition_description' => 'Получите больше бонусов',
                'link_url' => '/catalog',
                'button_text' => 'В каталог',
            ],
            [
                'title' => 'Повышенный кэшбек 10%',
                'description' => 'Получайте 10% кэшбека на товары из специальной подборки',
                'addition_description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'link_url' => '/catalog',
                'button_text' => 'Перейти к товарам',
            ],
        ];

        $profilePromotionIds = [3, 5]; // Первый - 3, второй - 5

        foreach (range(24, 25) as $i) {
            $index = $i - 24;
            $promotionId = $profilePromotionIds[$index];
            // Проверяем существование промоции
            if (!GlobalPromotion::find($promotionId)) {
                $promotionId = null;
            }

            $banner = Banner::updateOrCreate(
                [
                    'location' => BannerLocationEnum::ProfilePage,
                    'title' => $profileBannerTexts[$index]['title'],
                ],
                [
                    'title' => $profileBannerTexts[$index]['title'],
                    'description' => $profileBannerTexts[$index]['description'],
                    'addition_description' => $profileBannerTexts[$index]['addition_description'],
                    'link_url' => $profileBannerTexts[$index]['link_url'],
                    'button_text' => $profileBannerTexts[$index]['button_text'],
                    'banner_type' => BannerTypeEnum::PromotionBanner,
                    'location' => BannerLocationEnum::ProfilePage,
                    'global_promotion_id' => $promotionId,
                    'is_active' => true,
                    'only_for_new_client' => false,
                ]
            );
            $this->attachImageToBanner($banner, $i);
        }

        // PrivilegePage (папки 26-27)
        $privilegeBannerTexts = [
            [
                'title' => 'Повышенный кэшбек 10%!',
                'description' => 'Получайте 10% кэшбека на товары из специальной подборки',
                'addition_description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'link_url' => '/catalog',
                'button_text' => 'Перейти к товарам',
            ],
            [
                'title' => 'Повышенный кэшбек 10%.',
                'description' => 'Получайте 10% кэшбека на товары из специальной подборки',
                'addition_description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'link_url' => '/catalog',
                'button_text' => 'Перейти к товарам',
            ],
        ];

        foreach (range(26, 27) as $i) {
            $index = $i - 26;
            $promotionId = 5;
            // Проверяем существование промоции
            if (!GlobalPromotion::find($promotionId)) {
                $promotionId = null;
            }

            $banner = Banner::updateOrCreate(
                [
                    'location' => BannerLocationEnum::PrivilegePage,
                    'title' => $privilegeBannerTexts[$index]['title'],
                ],
                [
                    'title' => $privilegeBannerTexts[$index]['title'],
                    'description' => $privilegeBannerTexts[$index]['description'],
                    'addition_description' => $privilegeBannerTexts[$index]['addition_description'],
                    'link_url' => $privilegeBannerTexts[$index]['link_url'],
                    'button_text' => $privilegeBannerTexts[$index]['button_text'],
                    'banner_type' => BannerTypeEnum::PromotionBanner,
                    'location' => BannerLocationEnum::PrivilegePage,
                    'global_promotion_id' => $promotionId,
                    'is_active' => true,
                    'only_for_new_client' => false,
                ]
            );
            $this->attachImageToBanner($banner, $i);
        }

        // CartPage (папки 28-29)
        // 28 - обычный баннер для всех клиентов
        // 29 - только для новых клиентов
        $cartBannerTexts = [
            [
                'title' => 'Получите больше бонусов',
                'description' => '+3000 баллов при заказе от 3 000 ₽',
                'addition_description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'link_url' => '#',
                'button_text' => null,
                'only_for_new_client' => false,
            ],
            [
                'title' => 'Подарок при первом заказе',
                'description' => 'Сделайте первый заказ на сумму от 10 000 рублей и получите “Угольные брикеты 800 Degrees Extra Long Heat” в подарок!',
                'addition_description' => 'Подарок добавится к заказу автоматически',
                'link_url' => '#',
                'button_text' => null,
                'only_for_new_client' => true,
            ],
        ];

        foreach (range(28, 29) as $i) {
            $index = $i - 28;

            $promotionId = null;
            $bannerType = null;

            if ($i == 28) {
                $bannerType = BannerTypeEnum::PromotionBanner;
                $promotionId = 3;
                // Проверяем существование промоции
                if (!GlobalPromotion::find($promotionId)) {
                    $promotionId = null;
                }
            } else {
                $bannerType = BannerTypeEnum::FirstOrderGift;
                $promotionId = null;
            }

            $bannerData = [
                'title' => $cartBannerTexts[$index]['title'],
                'description' => $cartBannerTexts[$index]['description'],
                'addition_description' => $cartBannerTexts[$index]['addition_description'],
                'link_url' => $cartBannerTexts[$index]['link_url'],
                'button_text' => $cartBannerTexts[$index]['button_text'],
                'location' => BannerLocationEnum::CartPage,
                'banner_type' => $bannerType,
                'global_promotion_id' => $promotionId,
                'is_active' => true,
                'only_for_new_client' => $cartBannerTexts[$index]['only_for_new_client'],
            ];

            $banner = Banner::updateOrCreate(
                [
                    'location' => BannerLocationEnum::CartPage,
                    'title' => $cartBannerTexts[$index]['title'],
                    'banner_type' => $bannerType,
                ],
                $bannerData
            );
            $this->attachImageToBanner($banner, $i);
        }

        // MakingOrderPage (папки 30-31)
        $makingOrderBannerTexts = [
            [
                'title' => 'Получите больше бонусов!',
                'description' => '+3000 баллов при заказе от 3 000 ₽',
                'addition_description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'link_url' => '#',
                'button_text' => null,
                'only_for_new_client' => false,
            ],
            [
                'title' => 'Получите больше бонусов.',
                'description' => '+3000 баллов при заказе от 15 000 ₽',
                'addition_description' => 'Срок действия: 01.04.2025 – 15.04. 2025',
                'link_url' => '#',
                'button_text' => null,
                'only_for_new_client' => false,
            ],
        ];

        foreach (range(30, 31) as $i) {
            $index = $i - 30;
            $promotionId = 5;
            // Проверяем существование промоции
            if (!GlobalPromotion::find($promotionId)) {
                $promotionId = null;
            }

            $banner = Banner::updateOrCreate(
                [
                    'location' => BannerLocationEnum::MakingOrderPage,
                    'title' => $makingOrderBannerTexts[$index]['title'],
                ],
                [
                    'title' => $makingOrderBannerTexts[$index]['title'],
                    'description' => $makingOrderBannerTexts[$index]['description'],
                    'addition_description' => $makingOrderBannerTexts[$index]['addition_description'],
                    'link_url' => $makingOrderBannerTexts[$index]['link_url'],
                    'button_text' => $makingOrderBannerTexts[$index]['button_text'],
                    'banner_type' => BannerTypeEnum::PromotionBanner,
                    'location' => BannerLocationEnum::MakingOrderPage,
                    'global_promotion_id' => $promotionId,
                    'is_active' => true,
                    'only_for_new_client' => false,
                ]
            );
            $this->attachImageToBanner($banner, $i);
        }
    }

    protected function attachImageToBanner(Banner $banner, int $id): void
    {

        $sourcePath = public_path("images/Seeder/Banner/{$id}/image");

        // Получаем первый файл из папки
        $files = glob($sourcePath . '/*');

        if (empty($files)) {
            return;
        }

        $filePath = $files[0];
        $filename = basename($filePath);
        $storagePath = "Banner/{$banner->id}/image/{$filename}";

        Storage::disk('public')->put($storagePath, file_get_contents($filePath));

        File::create([
            'fileable_id' => $banner->id,
            'fileable_type' => Banner::class,
            'file_name' => $filename,
            'path' => $storagePath,
            'type_relation' => 'images',
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'size' => filesize($filePath),
            'disk' => 'public',
        ]);
    }
}
