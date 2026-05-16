<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Review;

use Alert\Enums\AlertTypeEnum;
use Alert\Models\Alert;
use App\Modules\Review\src\Models\ReviewAnswer;
use App\MoonShine\Resources\MoonShineUserResource;
use Illuminate\Database\Eloquent\Model;

use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Switcher;

/**
 * @extends ModelResource<ReviewAnswer>
 */
class ReviewAnswerResource extends ModelResource
{
    protected string $model = ReviewAnswer::class;

    protected string $title = 'Ответы на отзывы';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Админ','admin','name',MoonShineUserResource::class),
            BelongsTo::make('Отзыв','review','id',ReviewResource::class),
            Switcher::make('Активен','is_active')
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
                BelongsTo::make('Админ','admin','name',MoonShineUserResource::class),
                BelongsTo::make('Отзыв','review','id',ReviewResource::class),
                TinyMce::make('Ответ','text')->required(),
                Switcher::make('Активен','is_active')
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
            BelongsTo::make('Админ','admin','name',MoonShineUserResource::class),
            BelongsTo::make('Отзыв','review','id',ReviewResource::class),
            TinyMce::make('Ответ','text'),
            Switcher::make('Активен','is_active')
        ];
    }

    /**
     * @param ReviewAnswer $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function afterCreated(mixed $item): mixed
    {
        $user = $item->review->user;

        if ($user) {
            Alert::create([
                'user_id' => $user->id,
                'type' => AlertTypeEnum::ReviewReply,
                'title' => __('alerts.reviewReplyTitle'),
                'message' => __('alerts.reviewReplyMessage'),
                'button_text' => __('alerts.buttonDetails'),
                'button_link' => route('user.profile.reviews'),
            ]);
        }

        return $item;
    }


    protected function rules(mixed $item): array
    {
        return [
            'text' => ['required', 'string', function ($attribute, $value, $fail) {
                // Проверяем, что после удаления HTML тегов остается текст
                $textWithoutTags = strip_tags($value);
                $textWithoutTags = trim($textWithoutTags);
                if (empty($textWithoutTags)) {
                    $fail('Поле :attribute не может быть пустым.');
                }
            }],
        ];
    }
}
