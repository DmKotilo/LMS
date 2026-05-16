<?php

//TODO: Тест
use App\Modules\Pages\src\Http\Resources\RewardResource;
use App\Modules\Pages\src\Http\Resources\TeamResource;
use App\Modules\Review\src\Http\Resources\ReviewHomeCardResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Pages\Models\Page;
use Pages\Models\Reward;
use Pages\Models\Team;


Route::get('/company/about-us', function () {
    $teams = Team::orderBy('position')->get();
    $rewards = Reward::orderBy('position')->get();
    $reviews = \Review\Models\Review::where('status',\App\Modules\Review\src\Enums\ReviewStatusEnum::Approved)->where('show_about_us',true)->get();


    return Inertia::render('', [
        'teams' => TeamResource::collection($teams),
        'rewards' => RewardResource::collection($rewards),
        'reviews' => ReviewHomeCardResource::collection($reviews),

    ]);
})->middleware([])->name('page.about-us');

//// динамический роут для страниц из базы
//Route::get('/{slug}', function ($slug) {
//    $page = Page::where('slug', $slug)
//        ->where('is_active', true)
//        ->firstOrFail();
//
//
//    return Inertia::render('[additional]/ui/AdditionalTemplate', [
//        'seo_title' => $page->seo_title,
//        'seo_description' => $page->seo_description,
//        'content' => $page->content,
//    ]);
//})->where('slug', '^(?!docs|admin|api|vendor).+$')->middleware([])->name('page.show');
// динамический роут для страниц из базы
Route::get('/{slug}', function ($slug) {
    $page = Page::where('slug', $slug)
        ->where('is_active', true)
        ->firstOrFail();

    $seoDataArray = [
        'seo_title' => $page->seo_title,
        'seo_description' => $page->seo_description,
        'seo_keywords' => $page->keywords,
    ];

    return Inertia::render('[additional]/ui/AdditionalTemplate', [
        'seoData' => new SeoPageResource($seoDataArray),
        'content' => $page->content,
    ]);
})->where('slug', '^(?!docs|admin|api|vendor).+$')->middleware([])->name('page.show');


