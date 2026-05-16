<?php

namespace App\View\Moonshine;

use Illuminate\View\Component;

class CategoriesSelectedView extends Component
{
/*    public $url;
    public $defaultChecked = false;*/

    public function __construct(public $categoriesIdCheckedArray = [], public bool $defaultChecked = false)
    {
/*        $this->url = $url ?? url()->previous();*/
    }

    public function render()
    {
        return view('moonshine.components.categories-selected');
    }

}
