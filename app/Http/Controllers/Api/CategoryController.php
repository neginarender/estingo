<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{

    public function index()
    {
        $shortId = [];
        if(!empty($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
            if(is_null($shortId)){
                return response()->json([
                    'success'=>false,
                    //"message"=>"Service not available at this location"
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
