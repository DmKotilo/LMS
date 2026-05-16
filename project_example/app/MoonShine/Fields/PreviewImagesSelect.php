<?php

declare(strict_types=1);

namespace App\MoonShine\Fields;

use MoonShine\UI\Fields\Field;
use Closure;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use Illuminate\Contracts\Support\Renderable;
use Product\Models\Product;

class PreviewImagesSelect extends Field
{
    protected string $view = 'admin.fields.preview-image-select';

    protected function reformatFilledValue(mixed $data): mixed
    {
        return parent::reformatFilledValue($data);
    }

    protected function prepareFill(array $raw = [], ?DataWrapperContract $casted = null, int $index = 0): mixed
    {
        return parent::prepareFill($raw, $casted, $index);
    }

    protected function resolveValue(): mixed
    {
        return $this->toValue();
    }

    protected function resolvePreview(): Renderable|string
    {
        return (string) ($this->toFormattedValue() ?? '');
    }

    protected function resolveOnApply(): ?Closure
    {
        return function (mixed $item): mixed {
            $values = $this->getRequestValue() ?? [];
            return data_set($item, $this->getColumn(), $values);
        };
    }


    protected function viewData(): array
    {
        $data = $this->getData();

        if (! $data instanceof DataWrapperContract) {
            return ['item' => null];
        }

        $item = $data->getOriginal();

        if ($item instanceof Product) {
            $item->loadMissing('images');
        }

        return [
            'item' => $item,
        ];
    }
}
