<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\CategoryCollection;
use App\Models\BusinessSetting;
use App\Models\Category;
use Illuminate\Http\Request;
use DB;

class CategoryController extends Controller
{

    public function index()
    {

        // if(!empty($_SERVER['HTTP_SORTINGHUBID'])){

        //     // $distributorId = explode(',',$_SERVER['HTTP_DISTRIBUTORID']);

        //     $shortId = $_SERVER['HTTP_SORTINGHUBID'];

        //     if(is_null($shortId)){
        //         return response()->json([
        //             'success'=>false,
        //         ]);
        //     }
        //     if(!empty($shortId)){
        //         $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId)->pluck('product_id')->all();
        //         $categories = new CategoryCollection(
        //             Category::whereIn('id', function($query) use ($productIds){
        //                                 $query->select('category_id')
        //                                 ->from('products')
        //                                 ->whereIn('id',$productIds)->distinct();
        //                             })->select('id','name','banner','icon')->orderBy('sorting','asc')->where(['featured'=>1,'status'=>1])->get());
        //     }else{
        //         $categories = new CategoryCollection(Category::where(['featured'=>1,'status'=>1])->get());
        //     }
           

        // }else{
        //     $categories = new CategoryCollection(Category::where(['featured'=>1,'status'=>1])->orderBy('sorting','asc')->get());
        // }
        // return $categories;

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
