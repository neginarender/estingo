<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Address;
use Auth;
use App\Area;
use App\State;
use App\City;
use Session;
use DB;

class AddressController extends Controller
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        // $state = State::where('id',$request->state)->first('name');
        // $city = city::where('id',$request->city)->first('name');

        $address = new Address;
        if($request->has('customer_id')){
            $address->user_id = $request->customer_id;
        }
        else{
            $address->user_id = Auth::user()->id;
        }
        $address->name = $request->name;
        $address->address = $request->address;
        $address->country = "India";
        $address->state = $request->state;
        $address->city = $request->city;
        $address->postal_code = $request->pincode;
        $address->phone = $request->phone;
        $address->latitude = $request->lat;
        $address->longitude = $request->long;
        $address->save();
        flash(translate('Address has been added successfully.'));
        if($request->page==1)
        {
            return redirect()->route('checkout.shipping_info');
        }
        return redirect()->route('profile');
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
        $address = Address::findOrFail($id);
        if(!$address->set_default){
            $address->delete();
            return back();
        }
        flash(translate('Default address can not be deleted'))->warning();
        return back();
    }

    public function set_default($id){
        foreach (Auth::user()->addresses as $key => $address) {
            $address->set_default = 0;
            $address->save();
        }
        $address = Address::findOrFail($id);
        $address->set_default = 1;
        $address->save();

        return back();
    }


    public function set_location(REQUEST $request){
        if($request->has("autoloc")){
            setcookie('pincode',$request['postalCode'],time()+60*60*24*30,'/');
            setcookie('state',$request['locality'],time()+60*60*24*30,'/');
        }else{
            $state = City::select('name')->where('id',$request['city'])->first();
            $area = Area::select('area_name','pincode')->where('pincode',$request['pin'])->first();
            $pincode = $request['pin'];
            $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$pincode.'"]\')')
                ->selectRaw('user_id as sorting_hub_id')
                ->first('sorting_hub_id');
            setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
            setcookie('pincode',$area['pincode'],time()+60*60*24*30,'/');
            setcookie('state',$state['name'],time()+60*60*24*30,'/');
            setcookie('state_id',$request->state,time()+60*60*24*30,'/');
            setcookie('city_id',$request->city,time()+60*60*24*30,'/');
    
            Session::put(['pincode'=>$area['pincode'],'state'=>$state['name'],'state_id'=>$request->state,'city_id'=>$request->city]);

        }
        return back();

    }

    public function showMap($page){
        $page = decrypt($page);
        return view('showmap',compact('page'));
    }

    public function saveGiftAddress(Request $request){
        $data = [];
        $data['product_id'] = $request->product_id;
        $data['from'] = $request->from;
        $data['name'] = $request->name;
        $data['to'] = $request->to;
        $data['message'] = $request->message;
        Session::put('address'.$request->product_id,json_encode($data));
        if(Session::has('address'.$request->product_id)){
            return 1;
        }
        return 0;
    }

    public function updateAddressByCallcenter(Request $request){
        try{
            DB::beginTransaction();
                $user =\App\User::find($request->user_id);
               
                $user->city = $request->city;
                $user->postal_code = $request->pincode;
                $user->address = $request->address;
                $user->state = $request->state;
                $user->country = "India";
                $user->status = 1;
                $user->email_verification = 1;
                $user->is_old = 0;
                
                if($user->save()){
                    $createpeerpartner = \App\PeerPartner::updateOrCreate([
                        'user_id'=>$request->user_id
                    ],
                    [
                        'address' => $request->address,
                        'user_id' => $user->id,
                        "zone"=>$request->zone,
                        'old'=>0,
                        'pincode'=>$request->pincode
                    ]);

                    if($createpeerpartner){
                        \App\Address::where('user_id',$request->user_id)->delete();
                        //$updateuser = User::where('id',$request->user_id)->update(['peer_partner'=>1,'user_type'=>'partner']);
                        // create an address in address table
                        $address = new Address;
                        $address->name = $user->name;
                        $address->user_id = $user->id;
                        $address->address = $request->address;
                        $address->country = "India";
                        $address->city = $request->city;
                        $address->state = $request->state;
                        $address->postal_code = $request->pincode;
                        $address->phone = $user->phone;
                        $address->set_default = 1;
                        $address->block_id = $request->block_id;
                        $address->village = $request->village;
                        $address->save();
                    }
                }
                DB::commit();

                $shortId = \App\ShortingHub::whereRaw('JSON_CONTAINS(area_pincodes, \'["'.$request->pincode.'"]\')')->selectRaw('user_id as sorting_hub_id')->first('sorting_hub_id');
                setcookie('peer',$user->partner->code,time()+60*60*24*30,'/');
                setcookie('sid',encrypt($shortId['sorting_hub_id']),time()+60*60*24*30,'/');
                setcookie('pincode',$request->pincode,time()+60*60*24*30,'/');
                setcookie('city_name',$request->city,time()+60*60*24*30,'/');
                flash('Information updated successfully')->success();
                return back();
            }
            
        catch(\Exception $e){
            DB::rollback();
            flash('Something went wrong')->success();
            return back();
        }

    }
}
