<?php
namespace Faq\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Faq\src\Http\Resources\FaqResource;
use App\Modules\Faq\src\Http\Resources\FaqSectionResource;
use App\Modules\Faq\src\Models\FaqSection;
use Faq\Models\Faq;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{

    public function index()
    {
//        $sections = FaqSection::where('name','!=','Страница промоакций')->with(['faqs' => function ($query) {
//            $query->orderBy('position');
//        }])->orderBy('id')->get();
        $sections = FaqSection::where('is_default', false)
        ->with(['faqs' => function ($query) {
            $query->orderBy('position');
        }])->orderBy('id')->get();


        return Inertia::render('faq/ui/FaqPage', [
            "faqs" => FaqSectionResource::collection($sections)
        ]);
    }



}
