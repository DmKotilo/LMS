<?php

namespace App\Modules\SeoPage\src\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use SeoPage\Models\SeoPage;

class SeoPageResource extends JsonResource
{
    /**
     * Преобразовать ресурс в массив.
     *
     * @return array<string, mixed>
     */


    public static $model = SeoPage::class;
    public function toArray(Request $request): array
    {

        if (is_array($this->resource)) {
            return [
                'page_name' => $this->resource['page_name'] ?? null,
                'seo_title' => $this->resource['seo_title'] ?? null,
                'seo_description' => $this->resource['seo_description'] ?? null,
                'seo_keywords' => $this->resource['seo_keywords'] ?? null,
            ];
        }

        return [
            'page_name' => $this->page_name,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_keywords' => $this->seo_keywords,
        ];
    }
}
