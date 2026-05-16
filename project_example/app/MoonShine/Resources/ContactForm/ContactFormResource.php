<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ContactForm;

use App\Modules\ContactForm\src\Http\Enums\ContactFormStatusEnum;
use App\Modules\ContactForm\src\Http\Enums\ContactFormTypeEnum;
use ContactForm\Models\CallContactForm;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<CallContactForm>
 */
class ContactFormResource extends ModelResource
{
    protected string $model = CallContactForm::class;

    protected string $title = 'Заявки пользователей';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Имя клиента','name'),
            Phone::make('Номер телефона','phone'),
            Enum::make('Статус','status')->attach(ContactFormStatusEnum::class),
            Enum::make('Тип заявки','type')->attach(ContactFormTypeEnum::class),
            Date::make('Дата обращения','created_at'),
            Text::make('Почта','email'),


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
                Text::make('Имя клиента','name')->required(),
                Phone::make('Номер телефона','phone')->required(),
                Textarea::make('Комментарий','comment')->nullable()->readonly(),
                Text::make('Интервал звонка','time_interval'),
                Date::make('Дата обращения','created_at'),
                Text::make('Почта','email'),
                Text::make('Должность','post'),
                Enum::make('Статус','status')->attach(ContactFormStatusEnum::class)->required(),
                Enum::make('Тип заявки','type')->attach(ContactFormTypeEnum::class)->required(),
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
            Text::make('Имя клиента','name'),
            Phone::make('Номер телефона','phone'),
            Textarea::make('Комментарий','comment'),
            Text::make('Почта','email'),
            Text::make('Должность','post'),
            Text::make('Интервал звонка','time_interval'),
            Enum::make('Статус','status')->attach(ContactFormStatusEnum::class),
            Enum::make('Тип заявки','type')->attach(ContactFormTypeEnum::class),
            Date::make('Дата обращения','created_at'),

        ];
    }

    /**
     * @param CallContactForm $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }
}
