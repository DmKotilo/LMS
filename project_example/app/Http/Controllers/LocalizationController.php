<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Localization\Models\Lang;
use Localization\Models\Localization;
use ReflectionClass;
use ReflectionMethod;

class LocalizationController extends Controller
{

    public function update(Request $request,  $model,  $id)
    {
        $modelClass = 'App\\Models\\' . $model;

        if (!class_exists($modelClass)) {
            abort(404, 'Модель не найдена');
        }

        $modelItem = $modelClass::findOrFail($id);

        $relationMethods = collect((new ReflectionClass($modelClass))->getMethods(ReflectionMethod::IS_PUBLIC))
            ->filter(fn ($method) =>
                $method->class === $modelClass &&
                $method->getNumberOfParameters() === 0 &&
                is_subclass_of(optional(app($modelClass))->{$method->name}(), Relation::class)
            )
            ->pluck('name')
            ->flip();

        $plainFields = [];
        $relationFields = [];
        foreach ($request->except(['_method', 'model', 'id', 'translations']) as $key => $value) {
            if ($relationMethods->has($key)) {
                $relationFields[$key] = $value;
            } else {
                $plainFields[$key] = $value;
            }
        }

        if (!empty($plainFields)) {
            $modelItem->update($plainFields);
        }

        // связи
        foreach ($relationFields as $relation => $value) {
            $relationInstance = $modelItem->{$relation}();

            if ($relationInstance instanceof BelongsTo) {
                $foreignKey = $relationInstance->getForeignKeyName();
                $modelItem->{$foreignKey} = $value;
                $modelItem->save();
            }

            if ($relationInstance instanceof HasOne) {
                $related = $relationInstance->first() ?? $relationInstance->make();
                $related->fill($value)->save();
                $relationInstance->save($related);
            }

            if ($relationInstance instanceof HasMany) {
                $relationInstance->delete();
                foreach ($value as $item) {
                    $relationInstance->create($item);
                }
            }

            if ($relationInstance instanceof BelongsToMany) {
                $relationInstance->sync($value ?? []);
            }

            if ($relationInstance instanceof MorphOne) {
                $related = $relationInstance->first() ?? $relationInstance->make();
                $related->fill($value)->save();
                $relationInstance->save($related);
            }

            if ($relationInstance instanceof MorphMany) {
                $relationInstance->delete();
                foreach ($value as $item) {
                    $relationInstance->create($item);
                }
            }

            if ($relationInstance instanceof MorphToMany) {
                $relationInstance->sync($value ?? []);
            }

        }

        //обновление перводв
        $translations = $request->input('translations', []);
        foreach ($translations as $langId => $fields) {
            foreach ($fields as $field => $value) {
                $localization = Localization::updateOrCreate(
                    [
                        'localizationable_type' => $modelClass,
                        'localizationable_id' => $modelItem->id,
                        'lang_id' => $langId,
                        'field' => $field,
                    ],
                    [
                        'translate' => $value,
                    ]
                );

                logger()->info('Localization updated:', $localization->toArray());
            }
        }


        return back()->with('success', 'Успешно обновлено');
    }

    public function create(Request $request, string $model)
    {
        $modelClass = 'App\\Models\\' . $model;
        if (!class_exists($modelClass)) {
            abort(404, 'Модель не найдена');
        }
        $baseModel = new $modelClass();
        $baseModel->fill($request->except(['_token', '_method']))->save();


        //создание языков
        $langs = Lang::all();
        $translatedFields = $baseModel::getTransaledField();

        foreach ($langs as $lang) {
            foreach (array_keys($translatedFields) as $fieldName) {
                $valueFromModel = $baseModel->{$fieldName};

                Localization::updateOrCreate(
                    [
                        'localizationable_type' => get_class($baseModel),
                        'localizationable_id'   => $baseModel->id,
                        'lang_id'               => $lang->id,
                        'field'                 => $fieldName,
                    ],
                    [
                        'translate' => $valueFromModel,
                    ]
                );
            }
        }


        return redirect($request->get('resourceUrl'));

    }


}
