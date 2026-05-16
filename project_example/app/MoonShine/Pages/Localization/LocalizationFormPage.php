<?php

declare(strict_types=1);

namespace App\MoonShine\Pages\Localization;

use App\Models\Localization\Lang;
use App\Models\Localization\Localization;
use App\MoonShine\Resources\Localization\LocalizationResource;
use http\Env\Request;
use MoonShine\Contracts\UI\TableBuilderContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Collections\Fields;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

#[\MoonShine\MenuManager\Attributes\SkipMenu]
class LocalizationFormPage extends FormPage
{
    /**
     * @return array<string, string>
     */

    public function getTitle(): string
    {

        $resource = $this->getResource();
        $item = $resource->getItem() ? $resource->getItem()->where('id', request('resourceItem'))->first() : null;
        if ($item){
            return 'Edit';
        }else{
            return 'Create';

        }
    }

    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }
    /**
     * @return list<ComponentContract>
     */

    public function components(): array
    {
        $resource = $this->getResource();
        $resource->getUrl();
        $item = $resource->getItem() ? $resource->getItem()->where('id', request('resourceItem'))->first() : null;
        if ($item){
            $model = class_basename($resource->getModel());
            $tabs = (new LocalizationResource())->getTranslatedFieldsFormByItem($item);

            $url = route('translations.update', [
                'model' => $model,
                'id' => $item->id,
            ]);
            return [
                FormBuilder::make(
                )
                    ->action($url)
                    ->method(FormMethod::POST)
                    ->fields(
                        $resource->getFormFields()
                            ->push($tabs)
                            ->push(Hidden::make('_method')->setValue('POST'))
                            ->toArray()
                    )
                    ->name('crud')
                    ->fillCast($item, $resource->getCaster())
                    ->submit(__('moonshine::ui.save'), ['class' => 'btn-primary btn-lg']),
            ];
        }else{
            $model = class_basename($resource->getModel());
            $url = route('translations.create', [
                    'model' => $model,
                ]) . '?resourceUrl=' . urlencode($resource->getUrl());
            return [
                FormBuilder::make(
                )->action($url)
                    ->fields(
                        $this->getResource()
                            ->getFormFields()
                            ->push(
                                Hidden::make('_method')->setValue('POST')
                            )
                            ->toArray()
                    )
                    ->name('crud')
                    ->submit(__('moonshine::ui.save'), ['class' => 'btn-primary btn-lg']),
            ];
        }



    }
}
