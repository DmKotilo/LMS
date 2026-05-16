<?php

namespace App\Observers;

use App\Services\SitemapService;
use Category\Models\Category;

class CategoryObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Category "saved" event.
     */
    public function saved(Category $category): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('category', $category);
        $this->sitemapService->updateItem('category', $category, $shouldInclude);
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->sitemapService->updateItem('category', $category, false);
    }
}

