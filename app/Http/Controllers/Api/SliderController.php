<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\SliderCollection;
use App\Models\Slider;
use Cache;

class SliderController extends Controller
{
    public function index()
    {
        if(isset($_SERVER['HTTP_PINCODE']) && !empty($_SERVER['HTTP_PINCODE']) && isset($_SERVER['HTTP_CITY']) &&!empty($_SERVER['HTTP_CITY'])){
            $pincode = $_SERVER['HTTP_PINCODE'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
        
        }
                   
        if(!empty($shortId)){
            $sliders = \App\SortingHubSlider::where(['sorting_hub_id'=>$shortId['sorting_hub_id'],'published'=>1])->get();

        }
        else{
            $sliders = Slider::where('published',1)->get();
        }
        return new SliderCollection($sliders);
    }
}
