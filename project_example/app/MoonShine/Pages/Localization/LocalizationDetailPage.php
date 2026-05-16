<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Localization;

use App\MoonShine\Resources\Localization\LocalizationResource;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use Throwable;


/**
 * @extends DetailPage<ModelResource>
 */
class LocalizationDetailPage extends DetailPage
{
    /**
     * @return ComponentContract
     */
//    protected function fields(): iterable
//    {
//        return [];
//    }

    /**
     * @return ComponentContract
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return ComponentContract
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer()
        ];
    }

    /**
     * @return ComponentContract
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }
    public function components(): array
    {
        $resource = $this->getResource();
        $item = $resource->getItem()->where('id', request('resourceItem'))->firstOrFail();


        return [
            TableBuilder::make()->vertical()
                ->items([
                    $item
                ])
                ->fields(
                    $resource->getDetailFields()->all()
                )
        ];


    }
}
