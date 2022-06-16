<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\ShortingHub;
use App\Area;
use App\City;

class HomeController extends Controller
{
    public function mapped_cities()
    {
        $area_pincodes = ShortingHub::select('area_pincodes')->where('status',1)->get()->toArray();

        $mapped_pincode = array();

        if(count($area_pincodes) > 0){
            foreach ($area_pincodes as $key => $pincodes) {
                foreach ($pincodes as $key => $ids) {
                    foreach (json_decode($ids) as $key => $id) {
                        $mapped_pincode[] = $id;
                    }
                }
            }
        }
       
        $area = Area::select('district_id')->whereIn('pincode',$mapped_pincode)->groupBy('district_id')->get()->toArray();

        $cities = City::select('id','name')->whereIn('id', $area)->get();
        $data[0] = ['id'=>0,"name"=>"Select City"];
        foreach($cities as $key => $city){

        
            $data[] = ['id'=>$city->id,'name'=>$city->name];
            
        }
        //dd($data);
        return response()->json([
            'success'=>true,
            'data'=>$data
        ]);
    }

    public function get_area_for_delivery(Request $request)
    {
        $mapped_pins = get_mapped_pins();
        $area = Area::select('area_id','pincode')->where('district_id', $request->city_id)->whereIn('pincode', $mapped_pins)->groupBy('pincode')->get();
        return response()->json([
            'success'=>true,
            'data'=>$area
        ]);
    }

    public function get_mapped_pins(){

        $sorting_hub = ShortingHub::select('area_pincodes')->get()->toArray();
        $mapped_pin_code = array();

        if(count($sorting_hub) > 0){
            foreach ($sorting_hub as $key => $pin_codes) {
                foreach ($pin_codes as $key => $pins) {
                    foreach (json_decode($pins) as $key => $pin) {
                        $mapped_pin_code[] = $pin;
                    }
                }
            }
        }
        return $mapped_pin_code;
    }

    public function razorPayKey(){
        return response()->json([
            'key'=>env('RAZOR_KEY'),
            'secret'=>env('RAZOR_SECRET')
        ]);
    }

}
