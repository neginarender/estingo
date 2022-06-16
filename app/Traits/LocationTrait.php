<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Http\Controllers\Controller;
use App\LanguageSetting;
use App\DeviceManagement;

trait LocationTrait {

    public function location(){
        $data = (object)[];
        $data->location = false;
        $data->distributorId = [];
        $data->shortId = "";
        $data->productIds = [];
        $data->categoryIds = [];
        $data->peer_code = "";
        $data->subCategoryIds = [];
        $data->subSubCategoryIds = [];

        if(isset($_SERVER['HTTP_SORTINGHUBID'])){
             //$pincode = $_SERVER['HTTP_PINCODE'];
             $sortinghubid = $_SERVER['HTTP_SORTINGHUBID'];
             $distributorId = [];//\App\Distributor::whereRaw('json_contains(pincode, \'["' . $_SERVER['HTTP_PINCODE'] . '"]\')')->where('status',1)->pluck('id')->all();
             $shortId = ['sorting_hub_id'=>$sortinghubid];//\App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
             if(!empty($shortId)){
                $productIds = \App\MappingProduct::where('sorting_hub_id',$shortId['sorting_hub_id'])->pluck('product_id')->all();
                $categoryIds = \App\Product::where('published', '1')->whereIn('id',$productIds)->distinct()->pluck('category_id')->all();
                $subcategoryIds = \App\Product::where('published',1)->whereIn('id',$productIds)->distinct()->pluck('subcategory_id')->all();
                $subsubcategoryIds = \App\Product::where('published',1)->whereIn('id',$productIds)->distinct()->pluck('subsubcategory_id')->all();     
            }


        }
        else{
            $categoryIds = \App\Product::where('published', '1')->distinct()->pluck('category_id')->all();
            $productIds = array();
            $subcategoryIds = \App\Product::where('published',1)->distinct()->pluck('subcategory_id')->all();
            $subsubcategoryIds = \App\Product::where('published',1)->distinct()->pluck('subsubcategory_id')->all();
            $distributorId = [];
            $shortId = "";
        }

        $data->location = true;
        $data->distributorId = $distributorId;
        $data->shortId = $shortId;
        $data->productIds = $productIds;
        $data->categoryIds = $categoryIds;
        $data->subCategoryIds = $subcategoryIds;
        $data->subSubCategoryIds = $subsubcategoryIds;


        if(isset($_SERVER['HTTP_PEER']) && !empty($_SERVER['HTTP_PEER'])){
            $data->peer_code = $_SERVER['HTTP_PEER'];
        }

        return $data;

    }


    public function language($device_id = null, $user_id = null){
        $lang = 'en';
        $language = "";
        if($device_id != null || $user_id != null){
            $language = DeviceManagement::where(function($query) use($device_id,$user_id){
                if($device_id != null){
                    $query->where('device_id',$device_id);
                }elseif(($user_id != null)){
                    $query->where('user_id',$user_id);
                }
                return $query;

            })->first('language_code');
        }
        $lang = ($language != null)?$language->language_code:'en';
        return $lang;

    }


}