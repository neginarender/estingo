<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Region;
use App\State;
use App\City;
use App\User;
use App\Cluster;
use DB;
use App\Mail\MappingMailManager;
use Mail;
use App\Area;
use App\ShortingHub;
use App\Staff;

class UserMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        return view('user_map.user_mapping');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function get_mapping_form(Request $request)
    {
        // if($request->mapping_type == "cluster_hub") {
        //     return view('partials.cluster_hub_form');
        // }
        // elseif($request->mapping_type == "sorting_hub"){
        //     return view('partials.sorting_hub_form');
        // }
        // elseif($request->mapping_type == "peer_partner"){
        //     return view('partials.peer_partner_hub_form');
        // }
    }

    public function get_state_by_region_id(Request $request)
    {   
        $state = State::where('region_id', $request->region_id)->get();
        return $state;
    }

    public function get_state_by_cluster(Request $request)
    {   
        $state_id = Cluster::where('id',$request->cluster_id)->first('state_id');
        $state = State::whereIn('id', json_decode($state_id['state_id']))->get();
        return $state;
    }

    public function get_mapped_cities()
    {
        $mapped_city = Cluster::select('cities')->get()->toArray();
        $mapped_city_id = array();

        if(count($mapped_city) > 0){
            foreach ($mapped_city as $key => $city_ids) {
                foreach ($city_ids as $key => $ids) {
                    foreach (json_decode($ids) as $key => $id) {
                        $mapped_city_id[] = $id;
                    }
                }
            }
        }
        return $mapped_city_id;
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

    public function get_city_by_state_id(Request $request)
    {   
        $mapped_cities = $this->get_mapped_cities();
        $state = City::whereIn('state_id', $request->state_id)->whereNotIn('id', $mapped_cities)->get();
        return $state;
    }

    public function get_area_by_city_id(Request $request)
    {
        $mapped_pins = get_mapped_pins();
        $area = Area::where('district_id', $request->city_id)->whereNotIn('pincode', $mapped_pins)->get();
        return $area;
    }


    public function get_area_for_delivery(Request $request)
    {
        $mapped_pins = get_mapped_pins();
        $area = Area::where('district_id', $request->city_id)->whereIn('pincode', $mapped_pins)->groupBy('pincode')->get();
        return $area;
    }

    public function get_mapped_city_by_state_id(Request $request)
    {   
        $state = City::where('state_id', $request->state_id)->get();
        return $state;
    }

    public function get_cluster_by_city_id(Request $request)
    {
         $city_name = City::where('id', $request->city_id)->first();
         $cluster = Cluster::leftJoin('users', 'users.id', '=', 'clusters.user_id')
                    ->select('clusters.user_id', 'users.email')
                    ->where('cities', 'like', '%' .$city_name->id . '%')
                    ->get();
         
         return $cluster;
    }

    public function get_sortinghub_by_cluster_id(Request $request)
    {
        $sorting_hub = ShortingHub::leftJoin('users', 'users.id', '=', 'shorting_hubs.user_id')
                    ->select('shorting_hubs.user_id', 'users.email')
                    ->where('shorting_hubs.cluster_hub_id', $request->cluster_id)
                    ->get();

        return $sorting_hub;
    }

    public function get_cities_by_soroting_hub_id(Request $request){
        $cities = [];
        if(!is_null($request->sorting_hub_id)){
            $mapped_pins = \App\ShortingHub::where('user_id',$request->sorting_hub_id)->first();
        
            $districts = \App\Area::whereIn('pincode',json_decode($mapped_pins->area_pincodes))->pluck('district_id')->toArray();
            $cities = \App\City::whereIn('id',array_unique($districts))->select('id','name')->get();
        }
       
        return $cities;
    }

    public function get_cities_by_zone(Request $request){
        $cities = [];
        if(!is_null($request->zone)){
            $district = \App\Area::where('zone',$request->zone)->select('district_id')->where('status',1)->get();
           $cities = \App\City::whereIn('id',$district)->select('id','name')->get(); 
        }
       
        return $cities;
    }

    public function get_pincode_by_city(Request $request){
        $pincode = [];
        if(!is_null($request->district)){
            $pincode = \App\Area::where('district_id',$request->district)->select('pincode')->where('status',1)->distinct()->get();

        }
       
        return $pincode;
    }
}
