<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\User;

use App\MoonShine\Handlers\MassBonusHandler;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\MoonShine\Resources\Bonus\BonusHistoryResource;
use App\MoonShine\Resources\Bonus\BonusHistoryResourseResource;
use App\MoonShine\Resources\Bonus\LoyaltyResource;
use App\MoonShine\Resources\Bonus\UserLoyaltyLevelResource;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\HasOne;
use MoonShine\Laravel\Fields\Relationships\ModelRelationField;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FlexibleRender;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use User\Models\User;
use User\Models\UserLoyaltyLevel;

/**
 * @extends ModelResource<User>
 */
class UserResource extends ModelResource
{
    private const IMPERSONATOR_ROLE = 'Имперсонация';

    /** Email имперсонатора: .local — локаль, .ru — прод (совпадает с MoonshineSeeder) */
    private const IMPERSONATOR_EMAILS = [
        'impersonator@primefoods.local',
        'impersonator@primefoods.ru',
    ];

    protected string $model = User::class;
    protected string $title = 'Клиенты';
    protected bool $isAsync = false;

    protected function indexButtons(): ListOf
    {
        return parent::indexButtons()
            ->add(
                MassBonusHandler::make('Начислить бонусы')->setResource($this)->getButton()
            )
            ->add(
                ActionButton::make(
                    'Войти как',
                    static fn (mixed $original, ?\MoonShine\Contracts\Core\TypeCasts\DataWrapperContract $casted, ActionButtonContract $ctx): string => route(
                        'moonshine.users.impersonate',
                        ['user' => $casted?->getKey()]
                    )
                )
                    ->icon('arrow-right-on-rectangle')
                    ->canSee(static fn (...$args): bool => static::impersonationAllowed())
                    ->withConfirm(
                        title: 'Войти как пользователь',
                        content: static fn (): string => 'Вы уверены, что хотите войти на сайт от имени этого клиента?',
                        button: 'Войти'
                    )
            );
    }

    private static function impersonationAllowed(): bool
    {
        $admin = auth('moonshine')->user();
        if (! $admin) {
            return false;
        }

        if (in_array($admin->email, self::IMPERSONATOR_EMAILS, true)) {
            return true;
        }

        return (self::IMPERSONATOR_ROLE !== '')
            && ($admin->moonshineUserRole?->name === self::IMPERSONATOR_ROLE);
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->only(Action::VIEW,Action::UPDATE,Action::DELETE)
            // ->only(Action::VIEW)
            ;
    }

    private const PER_PAGE_MIN = 1;
    private const PER_PAGE_MAX = 100000;

    public function getQueryParamsKeys(): array
    {
        return array_merge(parent::getQueryParamsKeys(), ['per_page']);
    }

    public function getItemsPerPage(): int
    {
        $perPage = (int) ($this->getQueryParams()->get('per_page') ?: $this->itemsPerPage);
        $perPage = max(self::PER_PAGE_MIN, min(self::PER_PAGE_MAX, $perPage));
        return $perPage ?: $this->itemsPerPage;
    }

    /** Блок «Показать по N записей» выводится сверху через getMetrics (без своей IndexPage). */
    public function getMetrics(): array
    {
        return [
            FlexibleRender::make(
                view('moonshine.components.per-page-selector', [
                    'currentPerPage' => $this->getItemsPerPage(),
                    'min' => self::PER_PAGE_MIN,
                    'max' => self::PER_PAGE_MAX,
                ])
            ),
        ];
    }

    /** Обработчик должен быть в handlers(), иначе HandlerController не найдёт его по URL. */
    protected function handlers(): ListOf
    {
        return parent::handlers()->add(MassBonusHandler::make('Начислить бонусы'));
    }

    protected function search(): array
    {
        return [
            'name',
            'last_name',
            'email',
            'phone'
        ];
    }
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Имя','name'),
            Text::make('Фамилия','last_name'),
            Text::make('Почта','email'),
            Switcher::make('VIP-клиент','admin_verify'),
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
                Tabs::make([
                   Tabs\Tab::make('Данные клиента',[
                       Flex::make([
                           Text::make('Фамилия','last_name'),
                           Text::make('Имя','name')->nullable(),
                           Text::make('Отчество','second_name'),
                       ]),
                       Flex::make([
                           Text::make('Телефон','phone')->nullable()->mask('+7 (999) 999-99-99'),
                           Date::make('Подтверждение телефона','phone_verified_at')->nullable(),
                       ]),
                       Flex::make([
                           Text::make('Почта','email')->nullable(),
                           Date::make('Подтверждение почты','email_verified_at')->nullable(),
                       ]),
                       Text::make('yandex_id','yandex_id'),
                       Text::make('vk_id','vk_id'),
                   ]),
                    Tabs\Tab::make('Системные данные',[
                        Switcher::make('Аккаунт удалён','is_self_deleted')->default(false),
                        Switcher::make('VIP-клиент','admin_verify')->default(false),
                        Switcher::make('Заблокирован','is_blocked')->default(false),
                    ]),
                    Tabs\Tab::make('Программа привелегий',[
                        HasOne::make('Уровень лояльности','loyaltyLevel','name',UserLoyaltyLevelResource::class)
                            ->disableOutside(),
                        HasMany::make('История бонусов','bonusHistories','id',BonusHistoryResource::class)
                            ->disableOutside()
                            ->fields([
                                ID::make(),
                                Number::make('Количество','amount'),
                                Enum::make('Тип','type')->attach(BonusTypeEnum::class),
                                Enum::make('Статус','status')->attach(BonusStatusEnum::class),
                                Date::make('Дата активации','active_date')
                            ]),
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
            Text::make('Имя','name'),
            Text::make('Отчество','second_name'),
            Text::make('Фамилия','last_name'),
            Text::make('Телефон','phone'),
            Text::make('Доп телефон','phone_additional'),
            Text::make('Почта','email'),
            Text::make('Подтсверждение почты','email_verified_at'),
            Text::make('Подтверждение телефона','phone_verified_at'),
            Switcher::make('Аккаунт удалён','is_self_deleted'),
            Switcher::make('VIP-клиент','admin_verify'),
            Switcher::make('Заблокирован','is_blocked'),
            // HasOne поле убрано из detailFields, чтобы избежать конфликта при удалении
            // Поле доступно в formFields для редактирования

        ];
    }

    /**
     * @param User $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function beforeDeleting(mixed $item): mixed
    {

        if ($item instanceof User && $item->exists) {
            if (!$item->relationLoaded('loyaltyLevel')) {
                $item->load('loyaltyLevel');
            }

            if ($item->loyaltyLevel) {
                $item->loyaltyLevel->delete();
            }
        }

        return $item;
    }

    public function delete(mixed $item, ?FieldsContract $fields = null): bool
    {
        $item = $this->beforeDeleting($item);

        $fields ??= $this->getFormFields()->onlyFields(withApplyWrappers: true);

        $fields = $fields->filter(function ($field) {
            if ($field instanceof ModelRelationField
                && $field instanceof HasOne
                && $field->getRelationName() === 'loyaltyLevel') {
                return false;
            }
            return true;
        });

        $fields->fill($item->toArray(), $this->getCaster()->cast($item));

        $relationDestroyer = static function (ModelRelationField $field) use ($item): void {
            try {
                $relationItems = $item->{$field->getRelationName()};

                ! $field->isToOne() ?: $relationItems = collect([$relationItems]);

                $relationItems->each(
                    static fn (mixed $relationItem): mixed => $relationItem ? $field->afterDestroy($relationItem) : null
                );
                } catch (\Exception $e) {
            }
        };

        $fields->each(function (FieldContract $field) use ($item, $relationDestroyer): void {
            try {
                if ($field instanceof ModelRelationField
                    && $this->isDeleteRelationships()
                ) {
                    $relationDestroyer($field);
                } else {
                    $field->afterDestroy($item);
                }
                } catch (\Exception $e) {
            }
        });

        return (bool) tap($item->delete(), fn (): mixed => $this->afterDeleted($item));
    }
}
