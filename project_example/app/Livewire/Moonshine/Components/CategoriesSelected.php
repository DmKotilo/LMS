<?php

namespace App\Livewire\Moonshine\Components;

use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class CategoriesSelected extends Component
{

    public array $categoriesIdCheckedArray = [];

    public Collection $categoriesLvl1;
    public bool $defaultChecked = false;
    public string $search = "";
    public function mount($categoriesIdCheckedArray = [], $defaultChecked = false)
    {
        $this->categoriesLvl1 = \Category\Models\Category::getParentLvl()->get();
        $this->categoriesIdCheckedArray = $categoriesIdCheckedArray;
        $this->defaultChecked = $defaultChecked;

    }

/*    public function __construct( public array $categoriesIdCheckedArray = [],  public bool $defaultChecked = false)
    {
        parent::__construct();

    }*/

    public function render()
    {

        return view('livewire.moonshine.components.categories-selected', ['categoriesLvl1' => $this->categoriesLvl1]);
    }
}
