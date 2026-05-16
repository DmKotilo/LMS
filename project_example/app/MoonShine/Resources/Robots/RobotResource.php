<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Robots;

use Illuminate\Database\Eloquent\Model;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Robots\Models\Robot;

/**
 * @extends ModelResource<Robot>
 */
class RobotResource extends ModelResource
{
    protected string $model = Robot::class;

    protected string $title = 'Robots.txt';

    protected string $column = 'name';

    protected bool $createInModal = false;
    
    protected bool $editInModal = false;
    
    protected bool $detailInModal = false;

    /**
     * Редирект на форму редактирования единственной записи
     */
    public function getUrl(): string
    {
        $robot = Robot::getSingle();
        if ($robot) {
            return $this->getFormPageUrl($robot->getKey());
        }
        return parent::getUrl();
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Textarea::make('Содержимое robots.txt', 'content')
                    ->required()
                    ->hint('Содержимое файла robots.txt. При сохранении файл будет автоматически обновлен.')
                    ->customAttributes([
                        'rows' => 25,
                        'style' => 'font-family: monospace; font-size: 14px;'
                    ])
            ])
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            Textarea::make('Содержимое robots.txt', 'content')
                ->customAttributes([
                    'rows' => 25,
                    'style' => 'font-family: monospace; font-size: 14px;',
                    'readonly' => true
                ])
        ];
    }

    /**
     * @param Robot $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [
            'content' => ['required', 'string'],
        ];
    }

    /**
     * Действия после сохранения
     * Вызывается после сохранения модели в MoonShine
     */
    protected function afterSave(mixed $item, \MoonShine\Contracts\Core\DependencyInjection\FieldsContract $fields): mixed
    {
        if ($item instanceof Robot) {
            \Log::info('RobotResource: afterSave вызван', [
                'robot_id' => $item->id,
                'content_length' => strlen($item->content ?? ''),
                'content_preview' => substr($item->content ?? '', 0, 100)
            ]);

            // Перезагружаем модель из БД, чтобы убедиться, что у нас актуальные данные
            $item->refresh();

            \Log::info('RobotResource: модель перезагружена из БД', [
                'robot_id' => $item->id,
                'content_length_after_refresh' => strlen($item->content ?? '')
            ]);

            $result = $item->saveToFile();

            \Log::info('RobotResource: saveToFile завершен в afterSave', [
                'robot_id' => $item->id,
                'result' => $result
            ]);
        }

        return parent::afterSave($item, $fields);
    }

}

