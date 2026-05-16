<?php

namespace App\Observers;

use App\Services\SitemapService;
use Article\Models\Article;

class ArticleObserver
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Handle the Article "saved" event.
     */
    public function saved(Article $article): void
    {
        $shouldInclude = $this->sitemapService->shouldIncludeModel('article', $article);
        $this->sitemapService->updateItem('article', $article, $shouldInclude);
    }

    /**
     * Handle the Article "deleted" event.
     */
    public function deleted(Article $article): void
    {
        $this->sitemapService->updateItem('article', $article, false);
    }
}

