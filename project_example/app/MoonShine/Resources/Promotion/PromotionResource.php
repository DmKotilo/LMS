<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Promotion;

use App\Modules\Promotion\src\Enums\PromotionTypeEnum;
use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Product\ComboResource;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\System\CityResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Http\Request;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\RelationRepeater;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Collapse;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Notification\Enums\NotificationTypeEnum;
use Notification\Models\PromoNotification;
use Product\Models\Product;
use Promotion\Models\Promotion;
use User\Models\User;

/**
 * @extends ModelResource<Promotion>
 */
class PromotionResource extends ModelResource
{
    protected string $model = Promotion::class;

    protected string $title = 'Промоакции';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Название','name'),
            Enum::make('Тип','type')->attach(PromotionTypeEnum::class),
            Switcher::make('Активность','is_active'),
            Number::make('% скидки','sale_percent'),
            Date::make('Дата окончания','end_date')
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        $isEditPage = $this->getItem() != null;
        $modelClass = $this->getModel()::class;
        $model = $this->getModel();
        if ($isEditPage){
            $localizations = Localization::where('localizationable_type', $this->getModel()::class)
                ->where('localizationable_id', $this->getItem()->id)
                ->get()
                ->groupBy(fn ($item) => $item->lang_id . '.' . $item->field);
        }
        return [
            Box::make([
                ID::make(),
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       Text::make('Название','name')->required(),
                       Enum::make('Тип','type')->attach(PromotionTypeEnum::class)->required(),
                       Switcher::make('Активность','is_active')->default(false),
                       Number::make('% скидки','sale_percent')->required()->min(1)->max(99),
                       Date::make('Дата окончания','end_date')->required()
                   ]),
                    Tabs\Tab::make('Города',[
                        BelongsToMany::make('Города','cities','name',CityResource::class)
                            ->selectMode()
                            ->searchable()
                            ->required()
                   ]),
                    Tabs\Tab::make('Описание',[
                       Textarea::make('Короткое описание','short_description')->nullable(),
                   ]),
                    Tabs\Tab::make('Изображение',[
                        ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                            'style' => 'width: 200px; height: 200px;'
                        ]),
                    ]),
                    Tabs\Tab::make('Привязки товаров',[
//                        Collapse::make('Продукты',[
//                            BelongsToMany::make('Продукты','products','name',ProductResource::class)
//                                ->selectMode()
//                                ->searchable()
//                                ->valuesQuery(function($query, $field) {
//                                    $currentPromotion = $this->getItem();
//
//                                    if (!$currentPromotion || !$currentPromotion->id) {
//                                        return $query->whereDoesntHave('promotion');
//                                    }
//
//                                    return $query->where(function($q) use ($currentPromotion) {
//                                        $q->whereDoesntHave('promotion') // товары без акций
//                                        ->orWhereHas('promotion', function($q2) use ($currentPromotion) {
//                                            $q2->where('promotions.id', $currentPromotion->id); // товары уже этой акции
//                                    });
//                                });
//                            })->hint('показываются товары, которые: <br> 1)ещё не участвуют ни в одной акции  <br> 2)либо уже прикреплены к текущей акции'),
//                        ]),


                        Collapse::make('Продукты', [
                            BelongsToMany::make('Продукты', 'products', 'name', ProductResource::class)
                                ->selectMode()
                                ->placeholder('Начните вводить...')

                                // 1️⃣ Первоначальная выборка — первые 20 товаров
                                ->valuesQuery(function ($query, $field) {
                                    $currentPromotion = $this->getItem();

                                    $query->where('is_active', true)
                                        ->where(function ($q) use ($currentPromotion) {
                                            if (!$currentPromotion || !$currentPromotion->id) {
                                                $q->whereDoesntHave('promotion');
                                            } else {
                                                $q->whereDoesntHave('promotion')
                                                    ->orWhereHas('promotion', function ($q2) use ($currentPromotion) {
                                                        $q2->where('promotions.id', $currentPromotion->id);
                                                    });
                                            }
                                        });

                                    return $query->orderBy('id')->limit(20);
                                })

                                // 2️⃣ Асинхронный поиск
                                ->asyncSearch(
                                    'name',
                                    searchQuery: function ($query, $request, string $term, $field) {
                                        $currentPromotion = $this->getItem();

                                        return $query
                                            ->where('is_active', true)
                                            ->where(function ($q) use ($currentPromotion) {
                                                if (!$currentPromotion || !$currentPromotion->id) {
                                                    $q->whereDoesntHave('promotion');
                                                } else {
                                                    $q->whereDoesntHave('promotion')
                                                        ->orWhereHas('promotion', function ($q2) use ($currentPromotion) {
                                                            $q2->where('promotions.id', $currentPromotion->id);
                                                        });
                                                }
                                            })
                                            ->where(function ($q) use ($term) {
                                                $q->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($term) . '%'])
                                                    ->orWhere('article_number', (int) $term);
                                            })
                                            ->orderBy('id')
                                            ->limit(20);
                                    },
                                    limit: 20
                                )
                                ->hint('Не показываются товары, которые участвуют в любой другой акции'),
                        ]),



                        Collapse::make('Комбо-наборы',[
                            BelongsToMany::make('Комбо','combos','name',ComboResource::class)
                                ->selectMode()
                                ->searchable()
                                ->valuesQuery(function($query, $field) {
                                    $currentPromotion = $this->getItem();

                                    if (!$currentPromotion || !$currentPromotion->id) {
                                        return $query->whereDoesntHave('promotion');
                                    }

                                    return $query->where(function($q) use ($currentPromotion) {
                                        $q->whereDoesntHave('promotion') // товары без акций
                                        ->orWhereHas('promotion', function($q2) use ($currentPromotion) {
                                            $q2->where('promotions.id', $currentPromotion->id); // товары уже этой акции
                                        });
                                    });
                                })->hint('Не показываются комбо-наборы, которые участвуют в любой другой акции'),
                        ]),
                    ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => Promotion::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),

                    Tab::make('Seo', [
                        Text::make('Title','seo_title')->nullable(),
                        Text::make('Description','seo_description')->nullable(),

                        Text::make('Ключевые слова', 'keywords')
                            ->tags(10)
                    ]),
                ]),
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
            Text::make('Название','name')->required(),
            Enum::make('Тип','type')->attach(PromotionTypeEnum::class)->required(),
            Switcher::make('Активность','is_active')->required(),
            Number::make('% скидки','sale_percent')->required(),
            Date::make('Дата окончания','end_date')->required(),
            Textarea::make('Короткое описание','short_description')->nullable(),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            BelongsToMany::make('Продукты','products','name',ProductResource::class)
                ->selectMode()
                ->searchable()
                ->valuesQuery(function($query, $field) {
                    $currentPromotion = $this->getItem();

                    if (!$currentPromotion || !$currentPromotion->id) {
                        return $query->where('is_active', true)->whereDoesntHave('promotion');
                    }

                    return $query->where('is_active', true)
                        ->where(function($q) use ($currentPromotion) {
                            $q->whereDoesntHave('promotion') // товары без акций
                            ->orWhereHas('promotion', function($q2) use ($currentPromotion) {
                                $q2->where('promotions.id', $currentPromotion->id); // товары уже этой акции
                            });
                        });
                }),
            BelongsToMany::make('Комбо','combos','name',ComboResource::class)
                ->selectMode()
                ->searchable()
                ->valuesQuery(function($query, $field) {
                    $currentPromotion = $this->getItem();

                    if (!$currentPromotion || !$currentPromotion->id) {
                        return $query->whereDoesntHave('promotion');
                    }

                    return $query->where(function($q) use ($currentPromotion) {
                        $q->whereDoesntHave('promotion') // товары без акций
                        ->orWhereHas('promotion', function($q2) use ($currentPromotion) {
                            $q2->where('promotions.id', $currentPromotion->id); // товары уже этой акции
                        });
                    });
                }),
            BelongsToMany::make('Города','cities','name',CityResource::class),
        ];
    }
    protected function beforeUpdating(mixed $item): mixed
    {
        $translations = request('translations', []);
        $modelClass = $item::class;
        $modelId = $item->id;

        foreach ($translations as $langId => $fields) {
            foreach ($fields as $field => $translate) {
                Localization::updateOrCreate(
                    [
                        'localizationable_type' => $modelClass,
                        'localizationable_id' => $modelId,
                        'lang_id' => $langId,
                        'field' => $field,
                    ],
                    [
                        'translate' => $translate ?? '',
                    ]
                );
            }
        }

        return $item;
    }

    protected function afterCreated(mixed $item): mixed
    {
        $this->notifyUsersAboutPromotion($item);
        return $item;
    }

    protected function afterUpdated(mixed $item): mixed
    {
        $this->notifyUsersAboutPromotion($item);
        return $item;
    }

    protected function notifyUsersAboutPromotion(Promotion $promotion): void
    {
        if (!$promotion->is_active) {
            return;
        }

        $users = User::query()
            ->where('is_self_deleted', false)
            ->where('is_blocked', false)
            ->whereHas('setting', function ($q) {
                $q->where('sale_notifications', true);
            })
            ->get(['id', 'admin_verify']);

        $activityLogService = app(\App\Services\ActivityLogService::class);

        foreach ($users as $user) {
            $exists = PromoNotification::where('user_id', $user->id)
                ->where('promotion_id', $promotion->id)
                ->exists();

            if (!$exists) {
                // Для верифицированных: active_date = now()
                // Для неверифицированных: active_date = now() + 12 hours
                $activeDate = $user->admin_verify
                    ? now()
                    : now()->addHours(12);

                $notification = PromoNotification::create([
                    'user_id' => $user->id,
                    'promotion_id' => $promotion->id,
                    'type' => NotificationTypeEnum::Promo,
                    'is_read' => false,
                    'user_deleted' => false,
                    'active_date' => $activeDate,
                ]);

                // Логирование отправки уведомления о скидке
                $activityLogService->logNotificationSent(
                    $user,
                    \App\Models\UserActivityLog::NOTIFICATION_PROMO,
                    \App\Models\UserActivityLog::DELIVERY_STATUS_SENT,
                    $promotion->id,
                    ['notification_id' => $notification->id]
                );
            }
        }
    }

    /**
     * @param Promotion $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'end_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    if (strtotime($value) < strtotime(date('Y-m-d'))) {
                        $fail('Значение поля "Дата окончания" должно быть датой после или равной сегодняшней дате.');
                    }
                },
            ],
            'image' => isset($this->getItem()->image) ? ['nullable'] : ['required'],
        ];
    }



    public function validationMessages(): array
    {
        return [
            'email.required' => 'Требуется email'
        ];
    }



}
