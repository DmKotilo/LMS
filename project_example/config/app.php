<?php

use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'Europe/Moscow',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => 'ru',

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'ru'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Package Service Providers...
         */

        User\Providers\UserModuleServiceProvider::class,
        Authorization\Providers\AuthorizationModuleServiceProvider::class,
        \Api\Providers\ApiModuleServiceProvider::class,
        Address\Providers\AddressModuleServiceProvider::class,
        \Pages\Providers\PagesModuleServiceProvider::class,
        File\Providers\FileModuleServiceProvider::class,
        Attribute\Providers\AttributeModuleServiceProvider::class,
        Brand\Providers\BrandModuleServiceProvider::class,
        Tag\Providers\TagModuleServiceProvider::class,
        Category\Providers\CategoryModuleServiceProvider::class,
        Product\Providers\ProductModuleServiceProvider::class,
        Promotion\Providers\PromotionModuleServiceProvider::class,
        Review\Providers\ReviewModuleServiceProvider::class,
        Order\Providers\OrderModuleServiceProvider::class,
        Cart\Providers\CartModuleServiceProvider::class,
        Filter\Providers\FilterModuleServiceProvider::class,
        List\Providers\WhiteListModuleServiceProvider::class,
        Log\Providers\LogModuleServiceProvider::class,
        Bonus\Providers\BonusModuleServiceProvider::class,
        Coupon\Providers\CouponModuleServiceProvider::class,
        Loyalty\Providers\LoyaltyModuleServiceProvider::class,
        Localization\Providers\LocalizationModuleServiceProvider::class,
        ContactForm\Providers\ContactFormModuleServiceProvider::class,
        Analitic\Providers\AnaliticModuleServiceProvider::class,
        City\Providers\CityModuleServiceProvider::class,
        Article\Providers\ArticleModuleServiceProvider::class,
        Recipe\Providers\RecipeModuleServiceProvider::class,
        Home\Providers\HomeModuleServiceProvider::class,
        Banner\Providers\BannerModuleServiceProvider::class,
        Combo\Providers\ComboModuleServiceProvider::class,
        Faq\Providers\FaqModuleServiceProvider::class,
        UserSetting\Providers\UserSettingServiceProvider::class,
        Manufacturer\Providers\ManufacturerModuleServiceProvider::class,
        Alert\Providers\AlertModuleServiceProvider::class,
        Notification\Providers\NotificationModuleServiceProvider::class,
        Comment\Providers\CommentModuleServiceProvider::class,
        Referral\Providers\ReferralModuleServiceProvider::class,
        Combination\Providers\CombinationModuleServiceProvider::class,
        Setting\Providers\SettingModuleServiceProvider::class,
        SeoPage\Providers\SeoPageModuleServiceProvider::class,
        GlobalPromotion\Providers\GlobalPromotionModuleServiceProvider::class,
        Robots\Providers\RobotsModuleServiceProvider::class,




        \Catalog\Providers\CatalogModuleServiceProvider::class,
		\Order\Providers\OrderModuleServiceProvider::class,
		\Delivery\Providers\DeliveryModuleServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];
