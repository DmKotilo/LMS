<?php

namespace App\Observers;

use App\Services\SitemapService;
use Recipe\Models\Recipe;

class RecipeObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Recipe "saved" event.
     */
    public function saved(Recipe $recipe): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('recipe', $recipe);
        $this->sitemapService->updateItem('recipe', $recipe, $shouldInclude);
    }

    /**
     * Handle the Recipe "deleted" event.
     */
    public function deleted(Recipe $recipe): void
    {
        $this->sitemapService->updateItem('recipe', $recipe, false);
    }
}

