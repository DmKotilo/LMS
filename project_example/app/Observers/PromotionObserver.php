<?php

namespace App\Observers;

use App\Services\SitemapService;
use Promotion\Models\Promotion;

class PromotionObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Promotion "saved" event.
     */
    public function saved(Promotion $promotion): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('promotion', $promotion);
        $this->sitemapService->updateItem('promotion', $promotion, $shouldInclude);
    }

    /**
     * Handle the Promotion "deleted" event.
     */
    public function deleted(Promotion $promotion): void
    {
        $this->sitemapService->updateItem('promotion', $promotion, false);
    }
}

