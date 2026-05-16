<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\System;

use App\Enums\UserActivityStatusEnum;
use App\Enums\UserActivityTypeEnum;
use App\Models\UserActivityLog;
use App\MoonShine\Resources\User\UserResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Text;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;

/**
 * @extends ModelResource<UserActivityLog>
 */
class UserActivityLogResource extends ModelResource
{
    protected string $model = UserActivityLog::class;
    protected string $title = 'Логи действий пользователей';
    protected bool $isAsync = false;

    public function getItem(): ?Model
    {
        $item = parent::getItem();
        
        if ($item) {
            $item->load('user');
        }
        
        return $item;
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->only(Action::VIEW); // только просмотр, редактирование запрещено
    }

    protected function search(): array
    {
        return [
            'user.name',
            'user.email',
            'user.phone',
            'ip_address',
        ];
    }

    protected function queryTags(): array
    {
        return [
            QueryTag::make(
                'Регистрации',
                fn(Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_REGISTRATION)
            ),
            QueryTag::make(
                'Входы',
                fn(Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_LOGIN)
            ),
            QueryTag::make(
                'OAuth входы',
                fn(Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_SOCIAL_AUTH)
            ),
            QueryTag::make(
                'Восстановление пароля',
                fn(Builder $query) => $query->whereIn('activity_type', [
                    UserActivityLog::TYPE_PASSWORD_RESET_REQUEST,
                    UserActivityLog::TYPE_PASSWORD_RESET
                ])
            ),
            QueryTag::make(
                'Уведомления',
                fn(Builder $query) => $query->where('activity_type', UserActivityLog::TYPE_NOTIFICATION_SENT)
            ),
            QueryTag::make(
                'Успешные',
                fn(Builder $query) => $query->where('status', UserActivityLog::STATUS_SUCCESS)
            ),
            QueryTag::make(
                'Неудачные',
                fn(Builder $query) => $query->where('status', UserActivityLog::STATUS_FAILED)
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
            BelongsTo::make('Пользователь', 'user', 'name', UserResource::class)->nullable(),
            Enum::make('Тип действия', 'activity_type')->attach(UserActivityTypeEnum::class),
            Enum::make('Статус', 'status')->attach(UserActivityStatusEnum::class),
            Text::make('IP-адрес', 'ip_address'),
            Text::make('Социальная сеть', 'social_provider'),
            Text::make('Тип уведомления', 'notification_type'),
            Date::make('Дата и время', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                ID::make()->readonly(),
                BelongsTo::make('Пользователь', 'user', 'name', UserResource::class)
                    ->nullable()
                    ->readonly(),
                Enum::make('Тип действия', 'activity_type')
                    ->attach(UserActivityTypeEnum::class)
                    ->readonly(),
                Enum::make('Статус', 'status')
                    ->attach(UserActivityStatusEnum::class)
                    ->readonly(),
                Text::make('IP-адрес', 'ip_address')->readonly(),
                Text::make('User-Agent', 'user_agent')->readonly(),
                Text::make('Социальная сеть', 'social_provider')->readonly(),
                Text::make('Тип уведомления', 'notification_type')->readonly(),
                Text::make('Статус уведомления', 'notification_status')->readonly(),
                Text::make('Связанный ID', 'related_id')->readonly(),
                Json::make('Метаданные', 'metadata')->readonly(),
                Date::make('Дата создания', 'created_at')->withTime()->readonly(),
                Date::make('Дата обновления', 'updated_at')->withTime()->readonly(),
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
            Text::make('ФИО пользователя', 'user_fio', function ($item) {
                if (!$item->user) {
                    return 'Пользователь не указан';
                }
                
                $user = $item->user;
                $fio = trim(implode(' ', array_filter([
                    $user->last_name,
                    $user->name,
                    $user->second_name
                ])));
                
                return $fio ?: 'Не указано';
            })->readonly(),
            Text::make('Телефон пользователя', 'user_phone', function ($item) {
                if (!$item->user) {
                    return 'Пользователь не указан';
                }
                
                return $item->user->phone ?? 'Не указан';
            })->readonly(),
            Text::make('Почта пользователя', 'user_email', function ($item) {
                if (!$item->user) {
                    return 'Пользователь не указан';
                }
                
                return $item->user->email ?? 'Не указан';
            })->readonly(),
            Enum::make('Тип действия', 'activity_type')->attach(UserActivityTypeEnum::class),
            Enum::make('Статус', 'status')->attach(UserActivityStatusEnum::class),
            Text::make('IP-адрес', 'ip_address'),
            Text::make('User-Agent', 'user_agent'),
            Text::make('Социальная сеть', 'social_provider'),
            Text::make('Тип уведомления', 'notification_type'),
            Text::make('Статус уведомления', 'notification_status'),
            Text::make('Связанный ID', 'related_id'),
            Json::make('Метаданные', 'metadata'),
            Date::make('Дата создания', 'created_at')->format('d.m.Y H:i:s'),
            Date::make('Дата обновления', 'updated_at')->format('d.m.Y H:i:s'),
        ];
    }

    /**
     * @param UserActivityLog $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function beforeUpdating(mixed $item): mixed
    {
        // Запрещаем редактирование логов
        throw new \Exception('Редактирование логов действий пользователей запрещено.');
    }

    protected function beforeDeleting(mixed $item): mixed
    {
        // Запрещаем удаление логов
        throw new \Exception('Удаление логов действий пользователей запрещено.');
    }
}

