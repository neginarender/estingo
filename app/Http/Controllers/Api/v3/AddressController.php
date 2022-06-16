<?php

namespace App\Http\Controllers\Api\v3;

use App\Http\Resources\v3\AddressCollection;
use App\Address;
use Illuminate\Http\Request;
use DB;

class AddressController extends Controller
{
    protected $callUser;
    protected $callPassword;
    protected $did;

    public function __construct(){
        $this->callUser = env("EASY_GO_USER");
        $this->callPassword = env("EASY_GO_ACCESS_TOKEN");
        $this->did = env("DID");
    }


    public function addresses($id)
    {
        $list = Address::where('user_id',$id)->where('set_default',1)->first();
        if($list == NULL){
            $address_id = Address::where('user_id',$id)->orderBy('id','ASC')->pluck('id')->first();
            $setdefault = Address::where('id',$address_id)->update(['set_default'=>1]);
        }
        $add = Address::where('user_id', $id)->orderBy('set_default','desc')->get();
        return new AddressCollection($add);
    }

    public function createShippingAddress(Request $request)
    {
        $checkShortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $request->postal_code . '"]\')')->first();
        if(empty($checkShortingHub)){
            return response()->json([
            'message' => 'Sorry, Our Services are not available at this Pincode.'
            ]);

        }else{

            
            $address = new Address;
            $address->name = $request->name;
            $address->user_id = $request->user_id;
            $address->address = $request->address;
            $address->country = $request->country;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->postal_code = $request->postal_code;
            $address->phone = $request->phone;
            $address->tag = ucfirst($request->tag);
            $list = Address::where('user_id',$request->user_id)->where('set_default',1)->first();
            if($list == NULL){
                $address->set_default = 1;
            }
            $address->save();

            return response()->json([
                'message' => 'Shipping information has been added successfully'
            ]);
        }
    }

    public function deleteShippingAddress($id)
    {
        $address = Address::findOrFail($id);
        $address->delete();
        return response()->json([
            'message' => 'Shipping information has been deleted'
        ]);
    }

    public function callToCustomer(REQUEST $request){
        $token = json_decode($this->createToken());
        
        if($token->status == "success"){
            $dRs = json_decode($this->dailTo($token->API_TOKEN,$request));
            return response()->json([
                'message' => $dRs->msg
            ]);


        }else{
            return response()->json([
                'message' => $token->msg
            ]);

        }


    }

    protected function createToken(){
        $url = 'https://client.easygoivr.com/masterapi/gentoken';
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERPWD, $this->callUser.":".$this->callPassword);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
          ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }



    protected function dailTo($token,$request){
        $dailUrl = 'https://client.easygoivr.com/easygoapi/request/dial';
        $dcurl = curl_init($dailUrl);
        $data = [
            'exten' => $request['exten'],
            'number' => $request['number'],
            'did' => $this->did
          ];
        curl_setopt($dcurl,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($dcurl,CURLOPT_POST,true);
        curl_setopt($dcurl, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($dcurl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'API_TOKEN:'. $token
          ]);
        $dailRes = curl_exec($dcurl);
        curl_close($dcurl);
        return $dailRes;

    }

    //06-10-2021
    public function setAddressDefault(Request $request){

        $id = $request->address_id;
        $address = Address::where('user_id',$request->user_id)->where('id',$id)->first();

        if($address != NULL){

            $unsetAddress = Address::where('user_id',$request->user_id)->where('set_default','1')->update(['set_default'=>0]);
            $setdefault = Address::where('id',$id)->update(['set_default'=>1]);

            if($setdefault){
                return response()->json([
                    'success'=>true,
                    'message'=>"Default address set successfully."
                ]);
            }

            return response()->json([
                'success'=>false,
                'message'=>"Something went wrong."
            ]);
            
        }else{
           return response()->json([
                'success'=>false,
                'message'=>"Address not exist"
            ]);
        }
        
        return response()->json([
            'success'=>false,
            'message'=>"Something went wrong"
        ]);
    }

    public function checkShippingLocation(Request $request){
        $checkShortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $request->postal_code . '"]\')')->pluck('user_id')->first();
        if($checkShortingHub){
            if(isset($request->sortinghubid) && !empty($request->sortinghubid)){
                $shortId = $request->sortinghubid;
                if($checkShortingHub == $shortId){
                    return response()->json([
                        'success'=>true,
                        'location' =>"same",
                        'message'=>"Select location and shipping location are same."
                    ]);
                }else{
                    return response()->json([
                        'success'=>true,
                        'location' =>"different",
                        'message'=>"Select location and shipping location are different."
                    ]);
                }
            }
        }else{
            return response()->json([
                'success'=>true,
                'location' =>"not available",
                'message'=>"Sorry, Our Services are not available at this Pincode."
            ]);
        }
    }

    public function updateShippingAddress(Request $request){
        $checkShortingHub = \App\ShortingHub::whereRaw('json_contains(area_pincodes, \'["' . $request->postal_code . '"]\')')->first();
        if(empty($checkShortingHub)){
            return response()->json([
            'message' => 'Sorry, Our Services are not available at this Pincode.'
            ]);

        }else{
            DB::table('addresses')->where(['id'=>$request->id])->update([
                'name' => $request->name,
                'user_id' => $request->user_id,
                'address' => $request->address,
                'country' => $request->country,
                'state' => $request->state,
                'city' => $request->city,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone,
                'set_default' => $request->set_default,
                'tag' => ucfirst($request->tag)
            ]);
            

            return response()->json([
                'message' => 'Shipping information has been updated successfully'
            ]);
        }
    }

}
