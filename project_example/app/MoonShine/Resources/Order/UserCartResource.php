<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Order;

use App\MoonShine\Resources\User\UserResource;
use Cart\Models\UserCart;
use Illuminate\Database\Eloquent\Builder;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<UserCart>
 */
class UserCartResource extends ModelResource
{
    protected string $model = UserCart::class;
    protected string $title = 'Корзины';

    public function indexQuery(): Builder
    {
        return parent::indexQuery()
            ->with(['user'])
            ->withCount('items');
    }

    public function detailQuery(): Builder
    {
        return parent::detailQuery()
            ->with(['user', 'items.item']);
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->only(Action::VIEW, Action::UPDATE);
    }

    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Пользователь', 'user_display_name')->sortable(),
            Text::make('Session ID', 'session_id'),
            Text::make('Количество товаров', 'items_count')->sortable(),
        ];
    }

    protected function formFields(): iterable
    {
        return [
            Box::make([
                Tabs::make([
                    Tab::make('Информация о корзине', [
                        ID::make(),
                        BelongsTo::make('Пользователь', 'user', 'name', UserResource::class)
                            ->nullable()
                            ->creatable()
                            ->asyncSearch(),
                        Text::make('Session ID', 'session_id'),
                    ]),
                ]),
            ]),
        ];
    }

    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Пользователь', 'user_display_name'),
            Text::make('Session ID', 'session_id'),
            Text::make('Количество товаров', 'items_count'),
            HasMany::make('Товары в корзине', 'items', resource: CartItemResource::class)
                ->creatable(false)
                ->async()
        ];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make(
                'Корзины без пользователя (гости)',
                fn(Builder $query) => $query->whereNull('user_id')
            ),
            QueryTag::make(
                'Корзины с пользователем',
                fn(Builder $query) => $query->whereNotNull('user_id')
            ),
            QueryTag::make(
                'Корзины без товаров',
                fn(Builder $query) => $query->doesntHave('items')
            ),
            QueryTag::make(
                'Корзины с товарами',
                fn(Builder $query) => $query->has('items')
            ),
        ];
    }

    protected function rules(mixed $item): array
    {
        return [
            'user_id'    => ['nullable', 'exists:users,id'],
            'session_id' => ['required', 'string', 'max:255'],
        ];
    }
}
