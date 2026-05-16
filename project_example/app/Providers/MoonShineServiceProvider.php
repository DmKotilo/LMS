<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\Localization\LocalizationDetailPage;
use App\MoonShine\Pages\Localization\LocalizationFormPage;
use App\MoonShine\Pages\Localization\LocalizationIndexPage;
use App\MoonShine\Pages\CallMeAnalyticsPage;
use App\MoonShine\Pages\RecommendationsAnalyticsPage;
use App\MoonShine\Pages\OrdersAnalyticsPage;
use App\MoonShine\Pages\SalesAnalyticsPage;
use App\MoonShine\Resources\Address\AddressResource;
use App\MoonShine\Resources\Banner\BannerResource;
use App\MoonShine\Resources\Blog\Article\ArticleResource;
use App\MoonShine\Resources\Blog\Article\ArticleSelectionResource;
use App\MoonShine\Resources\Blog\File\FileResource;
use App\MoonShine\Resources\Blog\Recipe\RecipeResource;
use App\MoonShine\Resources\Blog\Recipe\RecipeStepResource;
use App\MoonShine\Resources\Blog\Recipe\RecipeVariantResource;
use App\MoonShine\Resources\Bonus\BonusHistoryResource;
use App\MoonShine\Resources\Bonus\LoyaltyResource;
use App\MoonShine\Resources\Bonus\UserLoyaltyLevelResource;
use App\MoonShine\Resources\Delivery\PickupLocationResource;
use App\MoonShine\Resources\LangStaticResource;
use App\MoonShine\Resources\Localization\LangResource;
use App\MoonShine\Resources\Localization\LangStaticKeyGroupResource;
use App\MoonShine\Resources\Localization\LangStaticKeyResource;
use App\MoonShine\Resources\Localization\LocalizationResource;
use App\MoonShine\Resources\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\Order\CartItemResource;
use App\MoonShine\Resources\Order\OrderItemResource;
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
use App\MoonShine\Resources\Product\ProductAttributeResource;
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
use App\MoonShine\Resources\Test\TestLocalizationResource;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\Core\DependencyInjection\ConfiguratorContract;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\Blog\Recipe\RecipeSelectionResource;
use App\MoonShine\Resources\Blog\Article\ArticleRelatedResource;
use App\MoonShine\Resources\Delivery\DeliveryZoneResource;
use App\MoonShine\Resources\Promotion\PromotionResource;
use App\MoonShine\Resources\Promotion\GlobalPromotionResource;
use App\MoonShine\Resources\Product\CityCounts\CityCountResource;
use App\MoonShine\Resources\ContactForm\ContactFormResource;
use App\MoonShine\Resources\Product\ProductAdminTagResource;
use App\MoonShine\Resources\Combination\CombinationResource;
use App\MoonShine\Resources\Combination\GarnishResource;
use App\MoonShine\Resources\Combination\SauceResource;
use App\MoonShine\Resources\Combination\DrinkResource;
use App\MoonShine\Resources\Setting\SettingResource;
use App\MoonShine\Resources\SeoPage\SeoPageResource;
use App\MoonShine\Resources\Robots\RobotResource;
use App\MoonShine\Resources\YandexFeed\YandexFeedResource;
class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  MoonShine  $core
     * @param  MoonShineConfigurator  $config
     *
     */
    public function boot(
        CoreContract $core,
        ConfiguratorContract $config,
        ColorManagerContract $colors,
    ): void
    {
        // Отключаем аутентификацию в локальном окружении
        if (config('app.env') === 'local') {
            $config->authDisable();
        }

        // $config->authEnable();
//        $colors->background('2, 40, 63');
//        $colors->content('20, 59, 82');
//        $colors->tableRow('2, 40, 63');// Строки таблицы
        $colors->borders('163, 152, 116'); // Границы
        $colors->dividers('163, 152, 116'); // Разделители
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                LangResource::class,
                LocalizationResource::class,
                TestLocalizationResource::class,
                ProductResource::class,
                ProductCategoryResource::class,
                ProductAttributeResource::class,
                ProductTagResource::class,
                ProductBrandResource::class,
                RecipeResource::class,
                RecipeStepResource::class,

                RecipeVariantResource::class,
                ArticleResource::class,
                FileResource::class,
                AttributeResource::class,
                BannerResource::class,
                UserResource::class,
                ComboResource::class,
                LangStaticResource::class,
                LangStaticKeyGroupResource::class,
                LangStaticKeyResource::class,
                PagesResource::class,
                MainCategoryResource::class,
                CityResource::class,
                FaqResource::class,
                FaqSectionResource::class,
                LoyaltyResource::class,
                UserLoyaltyLevelResource::class,
                RewardResource::class,
                TeamResource::class,
                OrderResource::class,

                UserCartResource::class,
                CartItemResource::class,

                OrderItemResource::class,
                CuttingProductResource::class,
                ManufacturerResource::class,
                ReviewResource::class,
                ReviewAnswerResource::class,
                ReviewReactionResource::class,
                OrderPromocodeResource::class,
                PickupLocationResource::class,
                AddressResource::class,
                SocialAuthSettingResource::class,
                TransactionMethodResource::class,
                CityPriceResource::class,
                RecommendedProductResource::class,
                RecommendedPageResource::class,
                RelatedProductResource::class,
                ArticleSelectionResource::class,
                RecipeSelectionResource::class,
                ArticleRelatedResource::class,
                DeliveryZoneResource::class,
                PromotionResource::class,
                GlobalPromotionResource::class,
                CityCountResource::class,
                BonusHistoryResource::class,
                ContactFormResource::class,
                ProductAdminTagResource::class,
                CombinationResource::class,
                GarnishResource::class,
                SauceResource::class,
                DrinkResource::class,
                SettingResource::class,
                SeoPageResource::class,
                RobotResource::class,
                YandexFeedResource::class,
                UserActivityLogResource::class,
            ])
            ->pages([
                ...$config->getPages(),
                LocalizationFormPage::class,
                LocalizationIndexPage::class,
                LocalizationDetailPage::class,
                CallMeAnalyticsPage::class,
                RecommendationsAnalyticsPage::class,
                OrdersAnalyticsPage::class,
                SalesAnalyticsPage::class,
            ])
        ;
    }
}
