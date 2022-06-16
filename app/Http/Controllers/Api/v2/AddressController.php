<?php

namespace App\Http\Controllers\Api\v2;

use App\Http\Resources\v2\AddressCollection;
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
        return new AddressCollection(Address::where('user_id', $id)->orderBy('set_default','desc')->get());
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
            $address->city = $request->city;
            $address->postal_code = $request->postal_code;
            $address->phone = $request->phone;
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
}
