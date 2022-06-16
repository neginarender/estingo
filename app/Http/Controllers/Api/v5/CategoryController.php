<?php

namespace App\Http\Controllers\Api\v5;

use App\Http\Resources\v5\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use Illuminate\Http\Request;
use DB;

class CategoryController extends Controller
{

    public function index()
    {

        $shortId = [];
        if(!empty($_SERVER['HTTP_SORTINGHUBID'])){
            $shortId['sorting_hub_id'] = $_SERVER['HTTP_SORTINGHUBID'];
        
            if(is_null($shortId)){
                return response()->json([
                    'success'=>false,
                ]);
            }
        }
        $categories = featured_categories($shortId);

        return new CategoryCollection($categories);
    }

    public function featured()
    {
        return new CategoryCollection(Category::where('featured', 1)->get());
    }

    public function home()
    {
        $homepageCategories = BusinessSetting::where('type', 'category_homepage')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        $categories = json_decode($homepageCategories->category);
        return new CategoryCollection(Category::find($categories));
    }

    
}
