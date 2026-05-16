<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Blog\Article;

use App\Modules\Blog\Selection\src\Enums\SelectionTypeEnum;
use App\MoonShine\CustomFields\ImageCustom;
use App\MoonShine\Resources\Blog\Category\SelectionCategoryResource;
use App\MoonShine\Resources\Blog\File\FileResource;
use App\MoonShine\Resources\Blog\Selection\SelectionArticleResource;
use App\MoonShine\Resources\Blog\Selection\SelectionResource;
use App\MoonShine\Resources\Product\ProductResource;
use App\MoonShine\Resources\Profile\ProfileFileResource;
use Article\Models\Article;
use File\Models\Files\File;
use Localization\Models\Lang;
use Localization\Models\Localization;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\BelongsToMany;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Fields\Relationships\RelationRepeater;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\AssetManager\InlineJs;
use Illuminate\Contracts\Database\Eloquent\Builder;
use MoonShine\TinyMce\Fields\TinyMce;
use MoonShine\UI\Components\Tabs;
use MoonShine\UI\Components\Tabs\Tab;
use MoonShine\UI\Fields\Field;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Preview;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Product\Models\Product;

/**
 * @extends ModelResource<Article>
 */
class ArticleResource extends ModelResource
{
    protected string $model = Article::class;
    protected bool $isAsync = false ;
    protected string $title = 'Статья';

    protected function search(): array
    {
        return [
            'title',
            'author',
            'mini_description',
            'seo_title',
            'seo_description',
            'keywords',
        ];
    }

    protected function onLoad(): void
    {
        $this->getAssetManager()
            ->prepend(InlineJs::make(<<<'JS'
            document.addEventListener('DOMContentLoaded', () => {
                // Проверка для одиночного изображения
                document.querySelectorAll('input[type="file"][name="image"]').forEach(input => {
                    input.addEventListener('change', function() {
                        const maxSize = 10 * 1024 * 1024;
                        if (this.files.length > 0 && this.files[0].size > maxSize) {
                            alert('Максимальный размер загружаемого файла 10МБ');
                            this.value = '';
                        }
                    });
                });

                // Проверка для множественных изображений
                document.querySelectorAll('input[type="file"][name="image[]"]').forEach(input => {
                    input.addEventListener('change', function() {
                        const maxSize = 10 * 1024 * 1024;
                        for (let i = 0; i < this.files.length; i++) {
                            if (this.files[i].size > maxSize) {
                                alert('Максимальный размер загружаемого файла 10МБ');
                                this.value = '';
                                break;
                            }
                        }
                    });
                });
            });
            JS
            ));
    }


    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->sortable(),
            ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Text::make('Заголовок', 'title'),
            Text::make('Автор', 'author'),
            Switcher::make('Опубликована', 'publish')
                ->updateOnPreview(),
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
            Tabs::make([
                Tab::make('Основная информация', [
                    ID::make()->sortable(),
                    Text::make('Заголовок', 'title')
                        ->required(),
                    Text::make('Автор', 'author'),
                    // Textarea::make('Описание', 'description'),
                    Textarea::make('Короткое описание', 'mini_description'),
                    Text::make('Теги', 'tags')
                        ->tags(10)->nullable()
                ]),
                Tab::make('Контент', [
                    TinyMce::make('Контент', 'text')
                        ->required(),
                    ]),
                Tab::make('Категории',[
                    BelongsToMany::make(
                        label: 'Категории',
                        relationName: 'selections',
                        formatted: 'title',
                        resource: ArticleSelectionResource::class
                    )->required()->selectMode()->searchable(),
                ]),
                Tabs\Tab::make('Изображения',[
                    ImageCustom::make('Изображения','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                        'style' => 'width: 200px; height: 200px;'
                    ]),
                ]),
                Tab::make('Публикация',[
                    Switcher::make('Опубликована', 'publish')
                        ->updateOnPreview(),
                    Switcher::make('Популярная статья', 'is_popular')->default(false),
                ]),
                Tab::make('Похожие статьи',[
                    HasMany::make('Похожие статьи','relatedArticles','title',ArticleRelatedResource::class)->creatable(),
                ]),
                Tab::make('Переводы', [
                    Preview::make('Переводы', 'preview', static fn() => view('moonshine.components.translate_component', [
                        'langs' => Lang::all(),
                        'translatedFields' => Article::getTransaledField(),
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

        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
            ImageCustom::make('Изображение','image')->itemAttributes(fn(string $filename, int $index = 0) => [
                'style' => 'width: 200px; height: 200px;'
            ]),
            Text::make('Заголовок', 'title')
                ->required(),
            TinyMce::make('Описание', 'description'),
            Textarea::make('Короткое описание', 'mini_description'),

            Switcher::make('Опубликована', 'publish')
                ->updateOnPreview(),
            Switcher::make('Популярная статья', 'is_popular'),
            Text::make('Теги', 'tags')
                ->tags(10),
            BelongsToMany::make(
                label: 'Категории',
                relationName: 'selections',
                formatted: 'name',
                resource: ArticleSelectionResource::class
            )->required()->creatable(),
            HasMany::make('Похожие статьи','relatedArticles','title',ArticleRelatedResource::class),

        ];
    }

    /**
     * @param Article $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */


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
    protected function rules(mixed $item): array
    {
        return [
            'mini_description' => ['required', 'string', 'max:255'],
        ];
    }
}
