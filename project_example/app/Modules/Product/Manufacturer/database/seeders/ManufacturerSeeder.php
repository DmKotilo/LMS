<?php

namespace Manufacturer\Database\Seeders;
use App\Modules\Product\Product\src\Enums\PriceTypeEnum;
use App\Modules\Product\Product\src\Enums\WeightTypeEnum;
use Attribute\Enums\InputTypeEnum;
use Attribute\Models\Attribute;
use Brand\Models\Brand;
use Category\Models\Category;
use File\Models\Files\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File as FileFacade;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Localization\Models\Lang;
use Localization\Models\Localization;
use Manufacturer\Models\Manufacturer;
use Product\Models\CuttingProduct;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Tag\Models\Tag;

class ManufacturerSeeder extends Seeder
{

    public function run()
    {
        if (Storage::disk('public')->exists('Manufacturer')) {
            Storage::disk('public')->deleteDirectory('Manufacturer');
        }
        $data = [
          [
              'name' => 'PRIMEBEEF',
              'content' => '
              <p class="mt-4"> PRIMEBEEF – премиальный бренд высококачественной мраморной говядины, в основе которого лежит особый подход к разведению, откорму и выращиванию животных в наиболее экологически благоприятных регионах Российской Федерации., </p>
              <p class="mt-3"> Осознанное потребление и бережное отношение к природным ресурсам – то, что отличает компанию с момента основания. Сфокусированность на одном направлении, а именно мраморной говядине, – одно из преимуществ бренда, которое позволило выйти в лидеры мясной индустрии. Для создания чистого продукта производитель замкнул на себе все циклы производства: от выращивания кукурузы для откорма до готового стейка. Премиальные или альтернативные отруба, стейки, котлеты для бургеров – каждому продукту уделяется максимум внимания. </p>
              '
          ],
          [
              'name' => 'PRIMEFOODS',
              'content' => '
              <p class="mt-4"> PRIMEFOODS – премиальный бренд высококачественной мраморной говядины, в основе которого лежит особый подход к разведению, откорму и выращиванию животных в наиболее экологически благоприятных регионах Российской Федерации., </p>
              <p class="mt-3"> Осознанное потребление и бережное отношение к природным ресурсам – то, что отличает компанию с момента основания. Сфокусированность на одном направлении, а именно мраморной говядине, – одно из преимуществ бренда, которое позволило выйти в лидеры мясной индустрии. Для создания чистого продукта производитель замкнул на себе все циклы производства: от выращивания кукурузы для откорма до готового стейка. Премиальные или альтернативные отруба, стейки, котлеты для бургеров – каждому продукту уделяется максимум внимания. </p>
              '
          ]
        ];

        foreach ($data as $item) {
            $item = Manufacturer::create([
                'name' => $item['name'],
                'content' => $item['content'],
            ]);
            $this->attachImagesToManufacturer($item, $item['id']);

        }
    }
    protected function attachImagesToManufacturer(Manufacturer $item, int $id): void
    {
        $sourcePath = public_path("images/Seeder/Manufacturer/{$id}/image/");

        $files = FileFacade::files($sourcePath);

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $storagePath = "Manufacturer/{$item->id}/image/{$filename}";

            Storage::disk('public')->put($storagePath, file_get_contents($file->getPathname()));

            File::create([
                'fileable_id' => $item->id,
                'fileable_type' => Manufacturer::class,
                'file_name' => $filename,
                'path' => $storagePath,
                'type_relation' => 'image',
                'extension' => $file->getExtension(),
                'size' => $file->getSize(),
                'disk' => 'public',
            ]);
        }
    }

}
