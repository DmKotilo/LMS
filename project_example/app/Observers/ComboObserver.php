<?php

namespace App\Observers;

use App\Services\SitemapService;
use Combo\Models\Combo;

class ComboObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Combo "saved" event.
     */
    public function saved(Combo $combo): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('combo', $combo);
        $this->sitemapService->updateItem('combo', $combo, $shouldInclude);
    }

    /**
     * Handle the Combo "deleted" event.
     */
    public function deleted(Combo $combo): void
    {
        $this->sitemapService->updateItem('combo', $combo, false);
    }
}

