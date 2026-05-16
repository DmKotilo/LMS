<?php

namespace App\Observers;

use App\Services\SitemapService;
use Combination\Models\Combination;

class CombinationObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Combination "saved" event.
     */
    public function saved(Combination $combination): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('combination', $combination);
        $this->sitemapService->updateItem('combination', $combination, $shouldInclude);
    }

    /**
     * Handle the Combination "deleted" event.
     */
    public function deleted(Combination $combination): void
    {
        $this->sitemapService->updateItem('combination', $combination, false);
    }
}

