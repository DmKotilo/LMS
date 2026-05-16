<?php

use App\Http\Controllers\ProfileController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Modules\Pages\src\Http\Resources\RewardResource;
use App\Modules\Pages\src\Http\Resources\TeamResource;
use App\Modules\Product\Review\src\Http\Resources\ReviewHomeCardResource;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Pages\Models\Page;
use Pages\Models\Reward;
use Pages\Models\Team;
use Review\Enums\ProductReviewStatus;
use Review\Models\ProductReview;

use App\Http\Controllers\Moonshine\ImpersonateUserController;
use App\Http\Controllers\Moonshine\LogsController;
use MoonShine\Laravel\Http\Middleware\Authenticate as MoonShineAuthenticate;


Route::get("/test_vk",function (){
    return view("oauth"); // тестовый oauth фронт
});
// Группа moonshine без auth — только сессия/CSRF. Auth добавляется отдельно для админских роутов.
Route::middleware(['moonshine', MoonShineAuthenticate::class])->group(function (){
    Route::post('/translations/update/{model}/{id}', [\App\Http\Controllers\LocalizationController::class, 'update'])->name('translations.update');
    Route::post('/translations/update/{model}', [\App\Http\Controllers\LocalizationController::class, 'create'])->name('translations.create');
    Route::get('/combo/product-price', [\App\MoonShine\Resources\Product\ComboResource::class, 'getProductPrice'])->name('combo.product-price');
    Route::get('/reviews/export', [\App\Http\Controllers\ReviewsExportController::class, 'export'])->name('moonshine.reviews.export');
    Route::get('/loyalty/export', [\App\Http\Controllers\LoyaltyReportController::class, 'export'])->name('moonshine.loyalty.export');
    Route::get('/call-me/export', [\App\Http\Controllers\CallMeExportController::class, 'export'])->name('moonshine.call-me.export');
    Route::get('/recommendations/export', [\App\Http\Controllers\RecommendationsExportController::class, 'export'])->name('moonshine.recommendations.export');
    Route::get('/orders/export', [\App\Http\Controllers\OrdersExportController::class, 'export'])->name('moonshine.orders.export');
    Route::get('/sales/export', [\App\Http\Controllers\SalesExportController::class, 'export'])->name('moonshine.sales.export');

    // MoonShine: войти на сайт от имени клиента (импёрсонация)
    Route::post('/moonshine/users/{user}/impersonate', ImpersonateUserController::class)
        ->name('moonshine.users.impersonate');

    // Просмотр логов (последние 500 строк) — только для авторизованных в админке
    Route::get('/logs/laravel', [LogsController::class, 'laravel'])->name('moonshine.logs.laravel');
    Route::get('/logs/bitrix24', [LogsController::class, 'bitrix24'])->name('moonshine.logs.bitrix24');
});


Route::get('/menu', function () {
    return Inertia::render('menu/ui/MenuPage');
})->middleware(['auth', 'verified'])->name('menu-page');

Route::get('/recipes', function () {
    return Inertia::render('recipes/ui/RecipesPage');
})->middleware(['auth', 'verified'])->name('recipes-page');

Route::get('/promo', function () {
    return Inertia::render('promo/ui/PromoPage');
})->middleware(['auth', 'verified'])->name('promo-page');

Route::get('/delivery', function () {
    return Inertia::render('delivery/ui/DeliveryPage');
})->middleware(['auth', 'verified'])->name('delivery-page');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified',\App\Http\Middleware\BlockIfAuthInertiaEnabled::class])->name('dashboard');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//// Маршрут для robots.txt
//Route::get('/robots.txt', function () {
//    $content = \Robots\Models\Robot::getContent();
//    return response($content)->header('Content-Type', 'text/plain');
//});

Route::fallback(function () {
    $status = 404;

    $shared = app(HandleInertiaRequests::class)->share(request());

    $message = 'Страница не найдена';

    return Inertia::render('404/ui/404Page', array_merge($shared, [
        'status' => $status,
        'message' => $message,
    ]))->toResponse(request())->setStatusCode($status);
});
