<?php

namespace App\Http\Controllers\Api\v4;

use App\DeliverySlot;
use App\Models\Cart;
use App\Models\Product;
use App\ShortingHub;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

class DeliveryslotController extends Controller
{

    public function index(Request $request)
    {

        $currentTime = Carbon::now();
        $currentTime = date('H:i:s',strtotime($currentTime));

        $todayDate = date('d-M, Y');
        $tommorowDate = date('d-M, Y', strtotime(date('Y-m-d'). ' + 1 day'));

        $data = array();
        $grocery_detail = array();
        $fresh_detail = array();

        $shipping_pincode = $request->postal_code;
        $shortingHubId = ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $shipping_pincode . '"]\')')->pluck('id')->first();                       
        
        $cart = Cart::where('device_id', $request->device_id)->latest()->get();

        $dairy = 0;
        $grocery = 0;
        if(count($cart) > 0){

        foreach ($cart as $key => $cartItem){

            $pro = Product::where('id',$cartItem['product_id'])->select('category_id','subcategory_id','subsubcategory_id')->first();

            if(isFreshInCategories($pro->category_id) || isFreshInSubCategories($pro->subcategory_id)){
                $dairy = 1;
            }else{
                $grocery = 1;
            }

        }
        $data['is_fresh'] = $dairy;
        $data['is_grocery'] = $grocery;

        if($grocery == 1){
            $todaySlot = DeliverySlot::where('status','1')->where('shorting_hub_id',$shortingHubId)->where('cut_off','>',date('H:i:s',strtotime($currentTime)))->where('type',2)->orderBy('delivery_time', 'ASC')->get();
            if(count($todaySlot) == 0){
                $grocery_detail['todaySlot_grocery'] = [];
            }else{
                foreach($todaySlot as $key => $value){

                    $grocery_detail['todaySlot_grocery'][$key] = $this->dateFormatConvert($value->delivery_time);
                }
            }


            $availSlotTom = DeliverySlot::where('status','1')->where('type',2)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();
            foreach($availSlotTom as $key => $value){
                $grocery_detail['tommorowSlot_grocery'][$key] = $this->dateFormatConvert($value->delivery_time);
            }
            $data['grocery_detail'] = $grocery_detail;
        }else{
            $grocery_detail['todaySlot_grocery'] = [];
            $grocery_detail['tommorowSlot_grocery'] = [];
            $data['grocery_detail'] = $grocery_detail;
        }

        if($dairy == 1){
            $availSlot = DeliverySlot::where('status','1')->where('cut_off','>',date('H:i:s',strtotime($currentTime)))->where('type', 1)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();
            if(count($availSlot) != 0){
                foreach($availSlot as $key => $value){
                    $date = $this->dateFormatConvert($value->delivery_time);
                    $fresh_detail['todaySlot_fresh'][$key] = $date;
                }
            }else{
                $fresh_detail['todaySlot_fresh'] = [];
            }
            $availSlotTom = DeliverySlot::where('status',1)->where('type',1)->where('shorting_hub_id',$shortingHubId)->orderBy('delivery_time', 'ASC')->get();
            if(count($availSlotTom) != 0){
                foreach($availSlotTom as $key => $value){   
                    $fresh_detail['tommorowSlot_fresh'][$key] = $this->dateFormatConvert($value->delivery_time);
                }
            }
            $data['fresh_detail'] = $fresh_detail;
        }else{
            $fresh_detail['todaySlot_fresh'] = [];
            $fresh_detail['tommorowSlot_fresh'] = [];
            $data['fresh_detail'] = $fresh_detail;
        }

        return response()->json([
            'success' => true,
            'message' => 'delivery slot',
            'data' => $data
        ]);
    }else{

        return response()->json([
            'success' => false,
            'message' => 'no slot available for empty cart',
            'data' => []
        ]);
        }
    }
    
    public function dateFormatConvert($data){
        $date = explode('-',$data);
        $start = date('h:i A',strtotime($date[0]));
        $end = date('h:i A',strtotime($date[1]));
        $date = $start.' - '.$end;
        return $date;
    }
    
    

}
