<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Banner;

use App\Modules\Banner\src\Http\Enums\BannerLocationEnum;
use App\Modules\Banner\src\Http\Enums\BannerTypeEnum;
use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Promotion\GlobalPromotionResource;
use Article\Models\Article;
use Banner\Models\Banner;
use Faker\Core\Number;
use Illuminate\Database\Eloquent\Model;

use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Enum;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Banner>
 */
class BannerResource extends ModelResource
{
    protected string $model = Banner::class;
    protected bool  $isAsync = false;
    protected string $title = 'Баннеры';

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        $modelClass = $this->getModel()::class;
        return [
            ID::make()->sortable(),
            Text::make('Заголовок','title'),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Switcher::make('Активен','is_active')
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
                Tabs::make([
                   Tabs\Tab::make('Основное',[
                       ID::make()->sortable(),
                       Text::make('Заголовок','title')->nullable(),
                       // При создании - Select с двумя опциями, при редактировании - Enum
                       $isEditPage 
                           ? Enum::make('Тип баннера','banner_type')->required()->attach(BannerTypeEnum::class)->default(null)
                           : Select::make('Тип баннера','banner_type')
                               ->options([
                                   BannerTypeEnum::PromotionBanner->value => BannerTypeEnum::PromotionBanner->toString(),
                                   BannerTypeEnum::FirstOrderGift->value => BannerTypeEnum::FirstOrderGift->toString(),
                               ])
                               ->required(),
                       BelongsTo::make('Глобальная акция','globalPromotion','title', GlobalPromotionResource::class)->nullable()->searchable(),
                       Enum::make('Место расположения','location')
                           ->attach(BannerLocationEnum::class)
                           ->when(!$isEditPage, fn($field) => $field->required())
                           ->when($isEditPage, fn($field) => $field->nullable()),
                       Switcher::make('Только для новых клиентов','only_for_new_client')->default(false),
                       Text::make('Текст кнопки','button_text')->nullable(),
                       Textarea::make('Ссылка кнопки','link_url')->nullable(),
                   ]) ,
                    Tabs\Tab::make('Изображения',[
                        ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                            'style' => 'width: 200px; height: 200px;'
                        ]),
                    ]),
                    Tabs\Tab::make('Описание',[
                        TinyMce::make('Описание','description')->nullable(),
                        Text::make('Дополнительное описание','addition_description')->nullable(),
                    ]),
                    Tabs\Tab::make('Публикация',[

                        Switcher::make('Активен','is_active')->default(false),
                        \MoonShine\UI\Fields\Number::make('Порядковый номер','order')->nullable()
                    ]),
                    Tab::make('Переводы', [
                        Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                            'langs' => Lang::all(),
                            'translatedFields' => Banner::getTransaledField(),
                            'translations' => $localizations,
                        ])),
                    ])->canSee(fn() => $isEditPage),
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
            Text::make('Заголовок','title')->nullable(),
            TinyMce::make('Описание','description')->nullable(),
            Text::make('Дополнительное описание','addition_description')->nullable(),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Enum::make('Тип баннера','banner_type')->attach(BannerTypeEnum::class),
            BelongsTo::make('Глобальная акция','globalPromotion','title', GlobalPromotionResource::class),
            Enum::make('Место расположения','location')->attach(BannerLocationEnum::class),
            Switcher::make('Только для новых клиентов','only_for_new_client'),
            Text::make('Текст кнопки','button_text'),
            Textarea::make('Ссылка кнопки','link_url'),
            Switcher::make('Активен','is_active'),
            \MoonShine\UI\Fields\Number::make('Порядковый номер','order')->nullable()

        ];
    }

    /**
     * @param Banner $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {

        return [
        ];
    }
    protected function beforeCreating(mixed $item): mixed
    {
        $bannerType = request()->banner_type;
        $isActive = request()->is_active;
        if ($bannerType == BannerTypeEnum::InfoBlock->value && $isActive) {
            $activeInfoBanners = Banner::where('banner_type', BannerTypeEnum::InfoBlock->value)
                ->where('is_active', true)
                ->orderBy('created_at')
                ->get();

            if ($activeInfoBanners->count() >= 3) {
                $oldestBanner = $activeInfoBanners->first();
                if ($oldestBanner) {
                    $oldestBanner->is_active = false;
                    $oldestBanner->save();
                }
            }

        } elseif ($bannerType == BannerTypeEnum::Novelty->value && $isActive) {
            $activeNoveltyBanner = Banner::where('banner_type', BannerTypeEnum::Novelty->value)
                ->where('is_active', true)
                ->first();

            if ($activeNoveltyBanner) {
                $activeNoveltyBanner->is_active = false;
                $activeNoveltyBanner->save();
            }
        } elseif ($bannerType == BannerTypeEnum::ComboPacks->value && $isActive) {
            $activeComboBanner = Banner::where('banner_type', BannerTypeEnum::ComboPacks->value)
                ->where('is_active', true)
                ->first();

            if ($activeComboBanner) {
                $activeComboBanner->is_active = false;
                $activeComboBanner->save();
            }
        }

        return $item;
    }
    protected function afterUpdated(mixed $item): mixed
    {
        $bannerType = request()->banner_type;
        $isActive = request()->is_active;

        if ($bannerType == BannerTypeEnum::InfoBlock->value && $isActive) {
            $activeInfoBanners = Banner::where('banner_type', BannerTypeEnum::InfoBlock->value)
                ->where('is_active', true)
                ->orderBy('created_at')
                ->get();

            if ($activeInfoBanners->count() >= 3) {
                $oldestBanner = $activeInfoBanners->first();
                if ($oldestBanner) {
                    $oldestBanner->is_active = false;
                    $oldestBanner->save();
                }
            }

        } elseif ($bannerType == BannerTypeEnum::Novelty->value && $isActive) {
            $activeNoveltyBanner = Banner::where('banner_type', BannerTypeEnum::Novelty->value)
                ->where('is_active', true)
                ->first();

            if ($activeNoveltyBanner) {
                $activeNoveltyBanner->is_active = false;
                $activeNoveltyBanner->save();
            }
        } elseif ($bannerType == BannerTypeEnum::ComboPacks->value && $isActive) {
            $activeComboBanner = Banner::where('banner_type', BannerTypeEnum::ComboPacks->value)
                ->where('is_active', true)
                ->first();

            if ($activeComboBanner) {
                $activeComboBanner->is_active = false;
                $activeComboBanner->save();
            }
        }

        return $item;
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
}
