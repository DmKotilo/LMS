<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Review;

use App\Modules\Review\src\Enums\ReviewStatusEnum;
use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\User\UserResource;
use Article\Models\Article;
use Combo\Models\Combo;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\HasOne;
use MoonShine\Laravel\Fields\Relationships\MorphTo;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Textarea;
use Product\Models\Product;
use Recipe\Models\Recipe;
use Review\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use User\Models\User;


/**
 * @extends ModelResource<Review>
 */
class ReviewResource extends ModelResource
{
    protected string $model = Review::class;

    protected string $title = 'Отзывы';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make(
                'Пользователь',
                'user',
                formatted: function(User $user): string {
                    $fioParts = array_filter([$user->last_name, $user->name, $user->second_name]);
                    $fio = !empty($fioParts) ? implode(' ', $fioParts) : null;
                    
                    if ($fio && $user->email) {
                        return "{$fio} {$user->email}";
                    } elseif ($user->email) {
                        return "пользователь {$user->email}";
                    } elseif ($fio && $user->phone) {
                        return "{$fio} {$user->phone}";
                    } elseif ($user->phone) {
                        return "пользователь {$user->phone}";
                    } else {
                        return "пользователь #{$user->id}";
                    }
                },
                resource: UserResource::class
            ),
            MorphTo::make('Элемент','item')->types([
                Product::class => ['name', 'Продукт'],
                Recipe::class => ['title', 'Рецепт'],
                Combo::class => ['name', 'Комбо-набор'],
                Article::class => ['title', 'Статья'],
            ]),
            Number::make('Оценка','rating'),
            Enum::make('Статус','status')->attach(ReviewStatusEnum::class),

        ];
    }

    /**
     * @return array<QueryTag>
     */
    protected function queryTags(): array
    {
        return [
            QueryTag::make('К продуктам', fn(Builder $query) => $query->where('item_type', Product::class)),
            QueryTag::make('К комбо', fn(Builder $query) => $query->where('item_type', Combo::class)),
            QueryTag::make('К рецептам', fn(Builder $query) => $query->where('item_type', Recipe::class)),
            QueryTag::make('К статьям', fn(Builder $query) => $query->where('item_type', Article::class)),
            QueryTag::make('Без ответа', fn(Builder $query) => $query->doesntHave('answer')),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make(),
                BelongsTo::make(
                    'Пользователь',
                    'user',
                    formatted: function(User $user): string {
                        $fioParts = array_filter([$user->last_name, $user->name, $user->second_name]);
                        $fio = !empty($fioParts) ? implode(' ', $fioParts) : null;
                        
                        if ($fio && $user->email) {
                            return "{$fio} {$user->email}";
                        } elseif ($user->email) {
                            return "пользователь {$user->email}";
                        } elseif ($fio && $user->phone) {
                            return "{$fio} {$user->phone}";
                        } elseif ($user->phone) {
                            return "пользователь {$user->phone}";
                        } else {
                            return "пользователь #{$user->id}";
                        }
                    },
                    resource: UserResource::class
                )
                    ->required()
                    ->placeholder('Начните вводить фамилию, имя или email...')
                    ->valuesQuery(function ($query, $field) {
                        return $query->select('id', 'name', 'last_name', 'second_name', 'email', 'phone')
                            ->orderBy('id')
                            ->limit(20);
                    })
                    ->asyncSearch(
                        'name',
                        searchQuery: function ($query, $request, string $term, $field) {
                            if ($term !== '') {
                                $termLower = mb_strtolower($term);
                                // Нормализуем телефон для поиска (оставляем только цифры)
                                $phoneDigits = preg_replace('/[^0-9]/', '', $term);
                                
                                // Определяем функцию нормализации телефона в зависимости от БД
                                $driver = DB::connection()->getDriverName();
                                $normalizePhone = match($driver) {
                                    'mysql' => "REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', '')",
                                    'mariadb' => "REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', '')",
                                    'pgsql' => "REGEXP_REPLACE(COALESCE(phone, ''), '[^0-9]', '', 'g')",
                                    default => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(phone, ''), '+', ''), ' ', ''), '(', ''), ')', ''), '-', ''), '.', ''), '_', ''), ' ', '')"
                                };
                                
                                $query->where(function ($q) use ($term, $termLower, $phoneDigits, $normalizePhone) {
                                    $q->whereRaw('LOWER(last_name) LIKE ?', ["%{$termLower}%"])
                                        ->orWhereRaw('LOWER(name) LIKE ?', ["%{$termLower}%"])
                                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$termLower}%"]);
                                    
                                    // Поиск по телефону (только если есть цифры в поисковом запросе)
                                    if (!empty($phoneDigits)) {
                                        $q->orWhereRaw("{$normalizePhone} LIKE ?", ["%{$phoneDigits}%"]);
                                    }
                                    
                                    if (is_numeric($term)) {
                                        $q->orWhere('id', (int) $term);
                                    }
                                });
                            }
                            return $query->select('id', 'name', 'last_name', 'second_name', 'email', 'phone')->orderBy('id')->limit(20);
                        },
                        limit: 20
                    ),
                MorphTo::make('Элемент','item')->types([
                    Product::class => ['name', 'Продукт'],
                    Recipe::class => ['title', 'Рецепт'],
                    Combo::class => ['name', 'Комбо-набор'],
                    Article::class => ['title', 'Статья'],
                ])->readonly(),
                ImageCustom::make('Изображения','images')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                    'style' => 'width: 200px; height: 200px;'
                ]),
//                Number::make('Оценка','rating')->readonly(),
//                Textarea::make('Отзыв','text')->readonly(),
                Date::make('Дата','created_at'),
                Number::make('Оценка','rating'),
                Textarea::make('Отзыв','text'),
                Enum::make('Статус','status')->attach(ReviewStatusEnum::class),
                Switcher::make('Показывать на главной','show_main')->default(false),
                Switcher::make('Показывать в каталоге','show_catalog')->default(false),
                Switcher::make('Показывать на "О нас"','show_about_us')->default(false),
                Switcher::make('Показывать на "Промоакции"','show_promotion_page')->default(false),
            ]),
               HasOne::make('Ответ на отзыв','answer','id',ReviewAnswerResource::class),
                HasMany::make('Реакции','reactions','type',ReviewReactionResource::class),

        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            BelongsTo::make(
                'Пользователь',
                'user',
                formatted: function(User $user): string {
                    $fioParts = array_filter([$user->last_name, $user->name, $user->second_name]);
                    $fio = !empty($fioParts) ? implode(' ', $fioParts) : null;
                    
                    if ($fio && $user->email) {
                        return "{$fio} {$user->email}";
                    } elseif ($user->email) {
                        return "пользователь {$user->email}";
                    } elseif ($fio && $user->phone) {
                        return "{$fio} {$user->phone}";
                    } elseif ($user->phone) {
                        return "пользователь {$user->phone}";
                    } else {
                        return "пользователь #{$user->id}";
                    }
                },
                resource: UserResource::class
            ),
            MorphTo::make('Элемент','item')->types([
                Product::class => ['name', 'Продукт'],
                Recipe::class => ['title', 'Рецепт'],
                Combo::class => ['name', 'Комбо-набор'],
                Article::class => ['title', 'Статья'],
            ]),
            ImageCustom::make('Изображения','images')->multiple()->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Number::make('Оценка','rating'),
            Textarea::make('Отзыв','text'),
            Enum::make('Статус','status')->attach(ReviewStatusEnum::class),
            Switcher::make('Показывать на главной','show_main'),
            Switcher::make('Показывать в каталоге','show_catalog'),
            Switcher::make('Показывать на "О нас"','show_about_us'),
            HasOne::make('Ответ на отзыв','answer','id',ReviewAnswerResource::class),
            HasMany::make('Реакции','reactions','type',ReviewReactionResource::class),
        ];
    }

    /**
     * @param Review $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
