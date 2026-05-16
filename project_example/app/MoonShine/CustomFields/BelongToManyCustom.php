<?php

namespace App\MoonShine\CustomFields;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Thumbnails;
use MoonShine\UI\Fields\Field;
use function PHPUnit\Framework\isInstanceOf;

class BelongToManyCustom extends BelongsToMany
{

/*       public function extraAttributes(Closure $callback): static
        {
            $callback =   fn(string $filename, int $index): ?FileItemExtra => new FileItemExtra(wide: false, auto: true, styles: 'width: 250px;');
            $this->parent::extraAttributes();
        }*/

    public function withCheckAll(): static
    {
        return $this->buttons([
            ActionButton::make('')
                ->onClick(static fn (): string => 'checkAll', 'prevent')
                ->primary()
                ->icon('check'),

            ActionButton::make('')
                ->onClick(static fn (): string => 'uncheckAll', 'prevent')
                ->error()
                ->icon('x-mark'),
        ]);
    }

    public function getCollectionValue(): EloquentCollection
    {
        return new EloquentCollection($this->getValue() ?? []);
    }

    protected function prepareFields(): FieldsContract
    {
        return $this->getFields()->prepareAttributes()->prepareReindexNames(
            parent: $this,
            before: fn (self $parent, Field $field): Field => (clone $field)
                ->setColumn("{$this->getPivotAs()}.{$field->getColumn()}")
                ->class('js-pivot-field')
                ->withoutWrapper(),
            performName: fn (string $name): string => str_replace($this->getRelationName(), $this->getPivotName(), $name),
        );
    }

}
