<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\ReviewsExportPage;
use App\MoonShine\Pages\LoyaltyReportPage;
use App\MoonShine\Pages\UserCartAnalyticsPage;
use App\MoonShine\Pages\CallMeAnalyticsPage;
use App\MoonShine\Pages\RecommendationsAnalyticsPage;
use App\MoonShine\Pages\OrdersAnalyticsPage;
use App\MoonShine\Pages\SalesAnalyticsPage;
use App\MoonShine\Resources\Address\AddressResource;
use App\MoonShine\Resources\Banner\BannerResource;
use App\MoonShine\Resources\Blog\Article\ArticleResource;
use App\MoonShine\Resources\Blog\Article\ArticleSelectionResource;
use App\MoonShine\Resources\Blog\Recipe\RecipeResource;
use App\MoonShine\Resources\Blog\Recipe\RecipeVariantResource;
use App\MoonShine\Resources\Bonus\BonusHistoryResource;
use App\MoonShine\Resources\Bonus\LoyaltyResource;
use App\MoonShine\Resources\Bonus\UserLoyaltyLevelResource;
use App\MoonShine\Resources\Delivery\PickupLocationResource;
use App\MoonShine\Resources\Localization\LangResource;
use App\MoonShine\Resources\Localization\LangStaticKeyGroupResource;
use App\MoonShine\Resources\Localization\LangStaticKeyResource;
use App\MoonShine\Resources\Order\OrderPromocodeResource;
use App\MoonShine\Resources\Order\OrderResource;
use App\MoonShine\Resources\Order\TransactionMethod\TransactionMethodResource;
use App\MoonShine\Resources\Order\UserCartResource;
use App\MoonShine\Resources\Pages\PagesResource;
use App\MoonShine\Resources\Pages\RewardResource;
use App\MoonShine\Resources\Pages\TeamResource;
use App\MoonShine\Resources\Product\AttributeResource;
use App\MoonShine\Resources\Product\CityPrice\CityPriceResource;
use App\MoonShine\Resources\Product\ComboResource;
use App\MoonShine\Resources\Product\CuttingProductResource;
use App\MoonShine\Resources\Product\MainCategoryResource;
use App\MoonShine\Resources\Product\Manufacturer\ManufacturerResource;
use App\MoonShine\Resources\Product\ProductBrandResource;
use App\MoonShine\Resources\Product\ProductCategoryResource;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\Product\ProductTagResource;
use App\MoonShine\Resources\Product\Recommend\RecommendedPageResource;
use App\MoonShine\Resources\Product\Recommend\RecommendedProductResource;
use App\MoonShine\Resources\Product\RelatedProducts\RelatedProductResource;
use App\MoonShine\Resources\Review\ReviewAnswerResource;
use App\MoonShine\Resources\Review\ReviewReactionResource;
use App\MoonShine\Resources\Review\ReviewResource;
use App\MoonShine\Resources\System\CityResource;
use App\MoonShine\Resources\System\FaqResource;
use App\MoonShine\Resources\System\FaqSectionResource;
use App\MoonShine\Resources\System\SocialAuthSettingResource;
use App\MoonShine\Resources\System\UserActivityLogResource;
use App\MoonShine\Resources\User\UserResource;
use App\MoonShine\Resources\YandexFeed\YandexFeedResource;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\UI\Components\{Layout\Layout};
use App\MoonShine\Resources\Blog\Recipe\RecipeSelectionResource;
use App\MoonShine\Resources\Blog\Article\ArticleRelatedResource;
use App\MoonShine\Resources\Delivery\DeliveryZoneResource;
use App\MoonShine\Resources\Promotion\PromotionResource;
use App\MoonShine\Resources\Promotion\GlobalPromotionResource;
use App\MoonShine\Resources\Product\CityCounts\CityCountResource;
use App\MoonShine\Resources\ContactForm\ContactFormResource;
use App\MoonShine\Resources\Combination\CombinationResource;
use App\MoonShine\Resources\Combination\GarnishResource;
use App\MoonShine\Resources\Combination\SauceResource;
use App\MoonShine\Resources\Combination\DrinkResource;
use App\MoonShine\Resources\Product\ProductAdminTagResource;
use App\MoonShine\Resources\Setting\SettingResource;
use App\MoonShine\Resources\SeoPage\SeoPageResource;
use App\MoonShine\Resources\Robots\RobotResource;
use Illuminate\Support\Facades\Auth;
use MoonShine\AssetManager\Js;

final class MoonShineLayout extends AppLayout
{

    protected function assets(): array
    {
        return [
            ...parent::assets(),
            Js::make('/moonshine-tabs.js'),
            Js::make('/moonshine-reorder-refresh.js'),
        ];
    }
    protected function getFooterMenu(): array
    {
        return [
            null
        ];
    }

    protected function getFooterCopyright(): string
    {
        return '© 2025 Made by <a href="https://zhuchenko.dev/" target="_blank">Zhuchenko’s development team</a>';
    }
    protected function menu(): array
    {
        return [

            MenuGroup::make('Система', [
                MenuItem::make('Настройки', SettingResource::class)->icon('adjustments-horizontal'),
                MenuGroup::make("SEO", [
                    MenuItem::make('Яндекс-фиды', YandexFeedResource::class)->icon('adjustments-horizontal'),
                    MenuItem::make('Robots.txt', RobotResource::class)->icon('document-text'),
                    MenuItem::make('SEO-страницы', SeoPageResource::class)->icon('globe-americas'),
                ])->icon("globe-americas"),
                MenuItem::make('История бонусов', BonusHistoryResource::class)->icon('calculator'),
                MenuGroup::make("Администраторы", [
                    MenuItem::make(
                        "Администраторы",
                        MoonShineUserResource::class
                    ),
                    MenuItem::make(
                        "Роли",
                        MoonShineUserRoleResource::class
                    ),
                ])->icon("user-plus"),
                MenuGroup::make("Система лояльности", [
                    MenuItem::make('Система лояльности', LoyaltyResource::class)->icon('presentation-chart-line'),
                    MenuItem::make('Уровни лояльности клиенктов', UserLoyaltyLevelResource::class)->icon('question-mark-circle'),
                    MenuItem::make('Отчеты по системе лояльности', LoyaltyReportPage::class)->icon('document-chart-bar'),
                ])->icon('presentation-chart-line'),
                MenuItem::make('Производители', ManufacturerResource::class)->icon('hand-raised'),
                MenuItem::make('Клиенты', UserResource::class)->icon('user-group'),
                MenuItem::make('Способы оплаты', TransactionMethodResource::class)->icon('credit-card'),
                MenuItem::make('Города', CityResource::class)->icon('truck'),
                MenuItem::make('Зоны доставки', DeliveryZoneResource::class)->icon('ellipsis-horizontal-circle'),

                MenuItem::make('Сервисы аутентификации', SocialAuthSettingResource::class)->icon('ellipsis-horizontal-circle'),

                MenuItem::make('Логи', UserActivityLogResource::class)->icon('document-text'),

                MenuItem::make('Адреса', AddressResource::class)->icon('map-pin'),
                MenuGroup::make("FAQ", [
                    MenuItem::make('FAQ', FaqResource::class)->icon("light-bulb"),
                    MenuItem::make('Разделы FAQ', FaqSectionResource::class)->icon('folder-open'),
                ])->icon("light-bulb"),

            ]),


//            MenuItem::make('Test/TestLocalizations', TestLocalizationResource::class),

            MenuGroup::make('Каталог', [
                MenuGroup::make('Комбо-наборы', [
                    MenuItem::make('Комбо-наборы', ComboResource::class)->icon('cube'),
                ]),
                MenuGroup::make('Категории', [
                    MenuItem::make('Баннеры категорий', MainCategoryResource::class)->icon('rectangle-group'),
                    MenuItem::make('Каталог', ProductCategoryResource::class)->icon('queue-list'),
                ]),
                MenuItem::make('Рекомендуемые товары', RecommendedPageResource::class)->icon('arrow-down-on-square-stack'),
                MenuItem::make('Аналитика блока рекомендаций', RecommendationsAnalyticsPage::class)->icon('chart-bar'),

                MenuItem::make('Продукты', ProductResource::class)->icon('shopping-cart'),
                MenuItem::make('Админ-теги', ProductAdminTagResource::class),
                MenuItem::make('Характеристики', AttributeResource::class)->icon('hashtag'),
                MenuItem::make('Тэги', ProductTagResource::class)->icon('tag'),
                MenuItem::make('Бренды', ProductBrandResource::class)->icon('list-bullet'),
            ])->icon('building-storefront'),


            MenuGroup::make(label: 'Контент', items: [
                MenuGroup::make(label: 'Рецепты и Статьи', items: [
                    MenuGroup::make(label: 'Блок рецептов', items: [
                        MenuItem::make(label: 'Рецепты', filler: RecipeResource::class)->icon('square-3-stack-3d')->icon('book-open'),
//                        MenuItem::make(label: 'Варианты приготовления', filler: RecipeVariantResource::class)->icon('table-cells')->icon('chevron-up'),
                        MenuItem::make('Категории рецептов', RecipeSelectionResource::class)->icon('arrow-up-on-square-stack'),

                    ])->icon('book-open'),
                    MenuGroup::make(label: 'Блок статей', items: [
                        MenuItem::make(label: 'Статьи', filler: ArticleResource::class)->icon('clipboard-document-list')->icon('clipboard-document-check'),
                        MenuItem::make('Категории статей', ArticleSelectionResource::class)->icon('arrow-up-on-square-stack'),

                    ])->icon('clipboard-document-check'),

                ]),
                MenuItem::make('Баннеры', BannerResource::class)->icon('photo'),

                MenuGroup::make('Страницы', [
                    MenuItem::make(label: 'Страницы', filler: PagesResource::class)->icon('code-bracket'),
                    MenuItem::make('Награды', RewardResource::class)->icon('trophy'),
                    MenuItem::make('Команда', TeamResource::class)->icon('users'),
                ])->icon('code-bracket'),
            ])->icon('share'),

            MenuGroup::make(label: 'Отзывы', items: [
                MenuItem::make('Отзывы', ReviewResource::class)->icon('bell-alert'),
                MenuItem::make('Ответы на отзывы', ReviewAnswerResource::class)->icon('bolt'),
                MenuItem::make('Аналитика по отзывам', ReviewsExportPage::class)->icon('chart-bar'),
            ])->icon('bell-alert'),
            MenuGroup::make('Локализация', [
                MenuItem::make('Языки и переводы', LangResource::class)->icon("language"),
                MenuItem::make('Ключи статических данных', LangStaticKeyResource::class)->icon('key'),
                MenuItem::make('Группы ключей', LangStaticKeyGroupResource::class)->icon('cube'),
            ])->icon('language'),


            MenuGroup::make(label: 'Заказ', items: [
                MenuItem::make('Заказы', OrderResource::class)->icon('shopping-bag'),
                MenuItem::make('Аналитика заказов', OrdersAnalyticsPage::class)->icon('chart-bar'),
                MenuItem::make('Аналитика продаж', SalesAnalyticsPage::class)->icon('presentation-chart-line'),
                MenuGroup::make('Корзины', [
                    MenuItem::make('Список', UserCartResource::class)->icon('shopping-bag'),
                    MenuItem::make('Аналитика', UserCartAnalyticsPage::class)->icon('chart-bar'),
                ]),
                MenuItem::make('Промокоды', OrderPromocodeResource::class)->icon('tag'),
                MenuItem::make('Пункты самовывоза', PickupLocationResource::class)->icon('map'),
            ])->icon('shopping-bag'),
            MenuGroup::make(label: 'Промоакции', items: [
                MenuItem::make('Промоакции', PromotionResource::class),
                MenuItem::make('Глобальные промоакции', GlobalPromotionResource::class),
            ])->icon('presentation-chart-line'),
            MenuGroup::make(label: 'Заявки обратной связи', items: [
                MenuItem::make('Заявки обратной связи', ContactFormResource::class)->icon('envelope-open'),
                MenuItem::make('Аналитика заявок "Позвонить мне"', CallMeAnalyticsPage::class)->icon('chart-bar'),
            ])->icon('envelope-open'),


            MenuItem::make('Реакции на отзывы', ReviewReactionResource::class)->canSee(fn() => false),

            MenuItem::make('Product/CuttingProducts', CuttingProductResource::class)->canSee(fn() => false),
            MenuItem::make('Product/CityPrice/CityPrices', CityPriceResource::class)->canSee(fn() => false),
            MenuItem::make('RecommendedProducts', RecommendedProductResource::class)->canSee(fn() => false),
            MenuItem::make('Product/RelatedProducts/RelatedProducts', RelatedProductResource::class)->canSee(fn() => false),
            MenuItem::make('Blog/Article/ArticleRelateds', ArticleRelatedResource::class)->canSee(fn() => false),
            MenuItem::make('Product/CityCounts/CityCounts', CityCountResource::class)->canSee(fn() => false),



            MenuGroup::make(label: 'Сочетания продуктов', items: [
                MenuItem::make('Комбинации', CombinationResource::class)->icon('arrows-pointing-in'),
                MenuItem::make('Гарниры', GarnishResource::class)->icon('book-open'),
                MenuItem::make('Соусы', SauceResource::class)->icon('funnel'),
                MenuItem::make('Напитки', DrinkResource::class)->icon('exclamation-circle'),
            ])->icon('arrows-pointing-in'),


        ];
    }

    /**
     * @param ColorManager $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        $colorManager->primary('163, 152, 116');
        $colorManager->secondary('163, 152, 116');
        // $colorManager->primary('#00000');
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
