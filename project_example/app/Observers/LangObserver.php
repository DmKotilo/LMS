<?php

namespace App\Observers;


use Localization\Models\Lang;
use Localization\Models\Localization;

class LangObserver
{
    /**
     * Handle the Lang "created" event.
     */
    public function created(Lang $lang): void
    {
        foreach (Localization::where('lang_id', Lang::first()->id)->get() as $localization) {
            $field = $localization->field;
            Localization::create([
                'localizationable_type' => $localization->localizationable_type,
                'localizationable_id' => $localization->localizationable_id,
                'lang_id' => $lang->id,
                'field' => $field,
                'translate' => $localization->localizationable->$field
            ]);
        }
    }

    /**
     * Handle the Lang "updated" event.
     */
    public function updated(Lang $lang): void
    {
        //
    }

    /**
     * Handle the Lang "deleted" event.
     */
    public function deleted(Lang $lang): void
    {
        //
    }

    /**
     * Handle the Lang "restored" event.
     */
    public function restored(Lang $lang): void
    {
        //
    }

    /**
     * Handle the Lang "force deleted" event.
     */
    public function forceDeleted(Lang $lang): void
    {
        //
    }
}
