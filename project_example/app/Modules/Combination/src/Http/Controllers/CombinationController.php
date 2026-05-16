<?php

namespace Combination\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Combination\src\Http\Resources\CombinationCardResource;
use App\Modules\Combination\src\Http\Resources\CombinationDetailResource;
use App\Modules\SeoPage\src\Http\Resources\SeoPageResource;
use Category\Http\Resources\CategoryItemTreeResource;
use Category\Http\Resources\CategoryResource;
use Category\Models\Category;
use Combination\Models\Combination;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Product\Models\Product;
use SeoPage\Models\SeoPage;


class CombinationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'category' => ['nullable', 'string'],
        ]);

        $is_combinations_group = true;


        $filterOn = false;


        $categoryTarget = null;
        if(isset($data['category']) && $data['category'] != null) {
            $categoryTarget = Category::where('slug', $data['category'])->first();
            if(!$categoryTarget) {
                throw new \Exception('Текущая категория не была найдена - '.$data['category']);
            }
            if($categoryTarget->children->count() > 0) {
                $categoriesCombinationGroup = $categoryTarget->children;
            } else {
                $categoriesCombinationGroup = new Collection([$categoryTarget]);
            }

        } else {
            $categoriesCombinationGroup = Category::getParentLvl()->get();
        }

        if($is_combinations_group) {
            $combinationsCollectionByCategories = new Collection;
            foreach ($categoriesCombinationGroup as $category) {
                $query = $category->combinations()->with('product')->where('is_active',true);

                $combinations = CombinationCardResource::collection($query->get());
                foreach ($combinations as $combination) {
                    $combination->category_group_name = $category->name;
                }

                $combinationsCollectionByCategories = $combinationsCollectionByCategories->merge($combinations);
            }

            $combinationsCollectionByCategoriesPaginator = $combinationsCollectionByCategories->paginate(24);
            $combinationsCollectionByCategoriesGroup = $combinationsCollectionByCategoriesPaginator->getCollection()->groupBy('category_group_name');
            $combinations = $combinationsCollectionByCategoriesPaginator->setCollection(($combinationsCollectionByCategoriesGroup));

        } else {

            $query = Combination::query()->where('is_active',true);

            // фильтр по категории (включая дочерние)
            $query = $query->with('combination_categories.category')
                ->whereHas('combination_categories.category', function (Builder $q) use ($categoriesCombinationGroup) {
                    return $q->whereIn('categories.id', $categoriesCombinationGroup->pluck('id')->toArray());
                });

            $combinations = $query->with('product', 'image')->get();

            $combinations = $combinations->paginate(24);

            $combinations = CombinationCardResource::collection($combinations);

            //строка-фикс, чтобы на фронте не было дублирования вложенности data и пагинации
            $combinations = $combinations->setCollection(New Collection($combinations->getCollection()));

        }

        //сео данные
        $seoData = SeoPage::where('slug','combinations')->first();

        return Inertia::render('combination/ui/CombinationPage', [
            'seoData' => $seoData ? new SeoPageResource($seoData) : null,            'category_target' => $categoryTarget ? CategoryItemTreeResource::make($categoryTarget) : null,
            'categories' => CategoryResource::collection(Category::getParentLvl()->get()),
            'combinations' => $combinations,
            'is_combinations_group' => $is_combinations_group,
        ]);
    }

    public function show(Request $request, Combination $combination)
    {
        $combination->loadMissing([
            'product.images',
            'garnishes.products',
            'sauces.products',
            'drinks',
            'image',
        ]);



        return Inertia::render('combination-detail/ui/CombinationDetailPage',[
            'combination' => new CombinationDetailResource($combination),
        ]) ;

    }


}
