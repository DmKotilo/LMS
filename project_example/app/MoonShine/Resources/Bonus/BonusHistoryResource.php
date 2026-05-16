<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Bonus;

use App\MoonShine\Resources\Order\OrderResource;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\Bonus\Bonus\src\Enums\BonusManualReasonEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusStatusEnum;
use App\Modules\Bonus\Bonus\src\Enums\BonusTypeEnum;
use App\MoonShine\Resources\User\UserResource;
use Bonus\Models\UserBonusHistory;
use Bonus\Services\BonusService;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\QueryTags\QueryTag;
use User\Models\User;

use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\MoonShineRequest;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use MoonShine\UI\Fields\Switcher;
use Illuminate\Support\Facades\DB;

/**
 * @extends ModelResource<UserBonusHistory>
 */
class BonusHistoryResource extends ModelResource
{
    protected string $model = UserBonusHistory::class;
    protected bool $isAsync = false;
    protected string $title = 'История бонусов пользователей';

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
        // Нормализуем телефон для поиска (оставляем только цифры)
        $phoneDigits = preg_replace('/[^0-9]/', '', $terms);
        
        // Определяем функцию нормализации телефона в зависимости от БД
        $driver = DB::connection()->getDriverName();
        $normalizePhone = match($driver) {
            'mysql' => "REGEXP_REPLACE(COALESCE(users.phone, ''), '[^0-9]', '')",
            'mariadb' => "REGEXP_REPLACE(COALESCE(users.phone, ''), '[^0-9]', '')",
            'pgsql' => "REGEXP_REPLACE(COALESCE(users.phone, ''), '[^0-9]', '', 'g')",
            default => "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(users.phone, ''), '+', ''), ' ', ''), '(', ''), ')', ''), '-', ''), '.', ''), '_', ''), ' ', '')"
        };
        
        $this->newQuery()->whereHas('user', function ($query) use ($termLower, $phoneDigits, $normalizePhone, $terms) {
            $query->where(function ($q) use ($termLower, $phoneDigits, $normalizePhone, $terms) {
                $q->whereRaw('LOWER(users.name) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(users.last_name) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(users.email) LIKE ?', ["%{$termLower}%"]);
                
                // Поиск по телефону (только если есть цифры в поисковом запросе)
                if (!empty($phoneDigits)) {
                    $q->orWhereRaw("{$normalizePhone} LIKE ?", ["%{$phoneDigits}%"]);
                }
                
                if (is_numeric($terms)) {
                    $q->orWhere('users.id', (int) $terms);
                }
            });
        });
    }

    protected function queryTags(): array
    {

        return [
            QueryTag::make(
                'Ручное начисление',
                fn(Builder $query) => $query->where('is_manual',true)->where('type',BonusTypeEnum::Accrual)
            ),
            QueryTag::make(
                'Ручное списание',
                fn(Builder $query) => $query->where('is_manual',true)->where('type',BonusTypeEnum::Withdrawal)
            ),
            QueryTag::make(
                'Ручное',
                fn(Builder $query) => $query->where('is_manual',true)
            ),
            QueryTag::make(
                'Списание',
                fn(Builder $query) => $query->where('type',BonusTypeEnum::Withdrawal)
            ),
            QueryTag::make(
                'Начисления',
                fn(Builder $query) => $query->where('type',BonusTypeEnum::Accrual)
            ),
            QueryTag::make(
                'Сгоревшие',
                fn(Builder $query) => $query->where('status',BonusStatusEnum::Expired)
            ),
        ];
    }
    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make(
                'Клиент',
                'user',
                formatted: function(User $user): string {
                    $parts = array_filter([$user->last_name, $user->name]);
                    return !empty($parts) ? implode(' ', $parts) : ($user->name ?? 'Без имени');
                },
                resource: UserResource::class
            ),
            Text::make('Количество','amount_floored'),
            Text::make('Остаток','remaining_amount_floored'),
            Enum::make('Тип','type')->attach(BonusTypeEnum::class),
            Enum::make('Статус','status')->attach(BonusStatusEnum::class),
            Switcher::make('Ручная операция','is_manual')->readonly(),
            BelongsTo::make('Заказ','order','id', OrderResource::class)->nullable(),
            Date::make('Дата активации','active_date')->format('d.m.Y H:i'),
            Date::make('Дата истечения','expires_at')->format('d.m.Y H:i'),
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
                    'Клиент',
                    'user',
                    formatted: fn(User $user): string => $user->display_name_with_email,
                    resource: UserResource::class
                )
                    ->required()
                    ->placeholder('Начните вводить фамилию, имя или email...')
                    ->valuesQuery(function ($query, $field) {
                        return $query->select('id', 'name', 'last_name', 'email', 'phone')
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
                            return $query->select('id', 'name', 'last_name', 'email', 'phone')->orderBy('id')->limit(20);
                        },
                        limit: 20
                    ),

                Number::make('Количество', 'amount')
                    ->min(1)
                    ->max(10000)
                    ->step(1)
                    ->required()
                    ->hint('Только целые числа. Сумма начисления или списания'),

                Enum::make('Тип','type')
                    ->attach(BonusTypeEnum::class)
                    ->required(),

                Enum::make('Причина', 'reason')
                    ->attach(BonusManualReasonEnum::class)
                    ->required()
                    ->hint('Выберите причину операции'),

                Textarea::make('Комментарий', 'comment')
                    ->nullable()
                    ->hint('Дополнительная информация об операции'),

                Date::make('Дата активации','active_date')
                    ->nullable()
                    ->withTime()
                    ->format('d.m.Y H:i')
                    ->hint('Когда бонусы станут доступны. Если не указана: через 24 часа от создания'),

                Date::make('Дата истечения','expires_at')
                    ->nullable()
                    ->withTime()
                    ->format('d.m.Y H:i')
                    ->hint('Когда бонусы сгорят. Если не указана: +90 дней от даты активации'),

                BelongsTo::make('Админ','admin','name', \App\MoonShine\Resources\MoonShineUserResource::class)
                    ->readonly(),
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
                'Клиент',
                'user',
                formatted: fn(User $user): string => $user->display_name_with_email,
                resource: UserResource::class
            ),
            Text::make('Количество','amount_floored'),
            Text::make('Остаток','remaining_amount_floored'),
            Enum::make('Тип','type')->attach(BonusTypeEnum::class),
            Enum::make('Статус','status')->attach(BonusStatusEnum::class),
            Switcher::make('Ручная операция','is_manual')->readonly(),
            Date::make('Дата активации','active_date')->format('d.m.Y H:i'),
            Date::make('Дата истечения','expires_at')->format('d.m.Y H:i'),
            Enum::make('Причина', 'reason')->attach(BonusManualReasonEnum::class),
            Textarea::make('Комментарий', 'comment')->readonly(),
            BelongsTo::make('Админ','admin','name', \App\MoonShine\Resources\MoonShineUserResource::class),
            BelongsTo::make('Заказ','order','id', OrderResource::class),
        ];
    }

    /**
     * @param UserBonusHistory $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'type' => ['required', 'in:' . implode(',', [
                    BonusTypeEnum::Accrual->value,
                    BonusTypeEnum::Withdrawal->value,
                ])],
            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
            ],
            'reason' => ['required', 'in:' . implode(',', array_keys(BonusManualReasonEnum::toArray()))],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function afterCreated(mixed $item): mixed
    {
        $bonusService = app(BonusService::class);

        // Получаем пользователя через связь (запись уже создана MoonShine'ом)
        $user = $item->user;
        if (!$user) {
            $item->delete();
            throw new \Exception('Пользователь не найден');
        }

        $adminId = auth()->guard('moonshine')->id();
        $type = $item->type;
        $reason = $item->reason;

        try {
            if ($type === BonusTypeEnum::Accrual) {
                // НАЧИСЛЕНИЕ: обновляем созданную запись, добавляя нужные поля
                // ВАЖНО: is_manual НЕ устанавливаем здесь, чтобы избежать двойных уведомлений
                // Он будет установлен в completeManualAccrual
                $completedHistory = $bonusService->completeManualAccrual($item, $user, $adminId);
                return $completedHistory;

            } else {
                // СПИСАНИЕ: удаляем запись MoonShine и создаём через сервис (FIFO + allocations)
                $amount = $item->amount;
                $comment = $item->comment;

                // Удаляем запись MoonShine
                $item->delete();

                // Создаём через сервис с правильной логикой
                $history = $bonusService->withdrawBonus(
                    user: $user,
                    amount: $amount,
                    isManual: true,
                    reason: $reason,
                    adminId: $adminId,
                    comment: $comment
                );

                return $history;
            }

            } catch (\Exception $e) {
            // Если ошибка - удаляем некорректную запись (если она еще существует)
            if ($item->exists) {
                $item->delete();
            }
            throw new \Exception('Ошибка при обработке бонусной операции: ' . $e->getMessage());
        }
    }

    protected function beforeCreating(mixed $item): mixed
    {
        // Проверяем баланс перед созданием записи о списании
        if ($item->type === BonusTypeEnum::Withdrawal && !$item->exists) {
            $user = $item->user;
            
            if (!$user) {
                throw new \Exception('Пользователь не найден');
            }

            $bonusService = app(BonusService::class);
            $availableBalance = $bonusService->getAvailableBalance($user);

            if ($availableBalance < $item->amount) {
                throw new \Exception("Недостаточно бонусов для списания. Доступно: {$availableBalance}, запрошено: {$item->amount}");
            }
        }

        return $item;
    }

    protected function beforeUpdating(mixed $item): mixed
    {
        // Запрещаем редактирование существующих записей
        throw new \Exception('Редактирование записей истории бонусов запрещено. Создайте новую операцию.');
    }
}
