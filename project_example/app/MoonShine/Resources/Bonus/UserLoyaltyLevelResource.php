<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Bonus;

use App\MoonShine\Resources\User\UserResource;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use User\Models\UserLoyaltyLevel;
use User\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * @extends ModelResource<UserLoyaltyLevel>
 */
class UserLoyaltyLevelResource extends ModelResource
{
    protected string $model = UserLoyaltyLevel::class;

    protected string $title = 'Уровни пользователей';

    protected function search(): array
    {
        return [
            'name',
            'last_name',
            'email',
            'phone'
        ];
    }

    protected function searchQuery(string $terms): void
    {
        $termLower = mb_strtolower($terms);
        
        $this->newQuery()->whereHas('user', function ($query) use ($termLower) {
            $query->where(function ($q) use ($termLower) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(last_name) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$termLower}%"]);
            });
        });
    }

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
                    return !empty($fioParts) ? implode(' ', $fioParts) : ($user->name ?? 'Без имени');
                },
                resource: UserResource::class
            ),
            BelongsTo::make('Уровень','loyaltyLevel','name',LoyaltyResource::class),
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
                                
                                $query->where(function ($q) use ($termLower) {
                                    $q->whereRaw('LOWER(last_name) LIKE ?', ["%{$termLower}%"])
                                        ->orWhereRaw('LOWER(name) LIKE ?', ["%{$termLower}%"])
                                        ->orWhereRaw('LOWER(second_name) LIKE ?', ["%{$termLower}%"])
                                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$termLower}%"]);
                                });
                            }
                            return $query->select('id', 'name', 'last_name', 'second_name', 'email', 'phone')->orderBy('id')->limit(20);
                        },
                        limit: 20
                    ),
                BelongsTo::make('Уровень','loyaltyLevel','name',LoyaltyResource::class),
            ])
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
            BelongsTo::make('Уровень','loyaltyLevel','name',LoyaltyResource::class),
        ];
    }

    /**
     * @param UserLoyaltyLevel $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
