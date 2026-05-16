<?php

namespace App\View\Composers;

use App\Services\BreadcrumbService;
use App\Services\ProductSchemaService;
use App\Services\OrganizationSchemaService;
use Illuminate\View\View;

class BreadcrumbComposer
{
    public function compose(View $view): void
    {
        // Breadcrumbs для всех страниц
        $breadcrumbService = app(BreadcrumbService::class);
        $customBreadcrumbs = request()->attributes->get('custom_breadcrumbs');
        $breadcrumbs = $customBreadcrumbs ?? $breadcrumbService->generate(request());
        
        $view->with('breadcrumbs_seo', $breadcrumbs);
        
        // Product Schema и Meta теги только для страниц продукта
        $productSchemaService = app(ProductSchemaService::class);
        $productSchema = null;
        $productMetaTags = [];
        
        if ($productSchemaService->isProductPage(request())) {
            $product = $productSchemaService->getProductFromRoute(request());
            if ($product) {
                $productSchema = $productSchemaService->generate($product);
                $productMetaTags = $productSchemaService->generateMetaTags($product);
            }
        }
        
        $view->with('product_schema', $productSchema);
        $view->with('product_meta_tags', $productMetaTags);
        
        // Organization Schema - статические данные компании (для всех страниц)
        $organizationSchema = OrganizationSchemaService::get();
        $view->with('organization_schema', $organizationSchema);
    }
}

