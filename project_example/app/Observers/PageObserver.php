<?php

namespace App\Observers;

use App\Services\SitemapService;
use Pages\Models\Page;

class PageObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Page "saved" event.
     */
    public function saved(Page $page): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('page', $page);
        $this->sitemapService->updateItem('page', $page, $shouldInclude);
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        $this->sitemapService->updateItem('page', $page, false);
    }
}

