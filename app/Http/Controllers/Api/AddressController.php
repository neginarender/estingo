<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\AddressCollection;
use App\Address;
use App\DeviceManagement;
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
            
            // foreach($unsetAddress as $key => $value){
            //     DB::table('addresses')
            //     ->where('id',$value->id)
            //     ->update(['set_default' => 0]);
            // }

            // DB::table('addresses')
            //     ->where('user_id', $request->user_id)
            //     ->where('id',$request->address_id)
            //     ->update(['set_default' => 1]);
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

    final public function languageSetting(REQUEST $request){
        $status = true;
        $data = array();
        $message = "";
        try{

            if($request->request_type == 'set'){
                if(!empty($request->device_id) || !empty($request->user_id)){
                    if(!empty($request->lang_code)){
                        // if(!empty($request->device_id) && !empty($request->user_id)){
                            $data = DeviceManagement::where('device_id',$request->device_id)->where('user_id',$request->user_id)->first();
                            
                            if($data != null){
                                $data->language_code = $request->lang_code;
                                if($data->save()){
                                    $message = "Language has been changed 1.";
                                }else{
                                    $message = "Something Went Wrong 1.";
                                }
                            }else{
                                $data = DeviceManagement::where(['device_id'=>$request->device_id])->first();
                                if($data != null){
                                    $data->language_code = $request->lang_code;
                                    if(empty($data->user_id)){
                                        $data->user_id = $request->user_id;
                                    }
                                    if($data->save()){
                                        $message = "Language has been changed 2.";
                                    }else{
                                        $message = "Something Went Wrong 2.";
                                    }

                                }else{
                                    $data = DeviceManagement::where('user_id',$request->user_id)->where('platform',$request->platform)->first();
                                    $data->language_code = $request->lang_code;
                                    if(empty($request->device_id)){
                                        $data->device_id = $request->device_id;
                                    }
                                    
                                    if($data != null && $request->user_id != null){
                                        $data->save();
                                        $message = "Language has been changed 3.";
                                    }else{
                                        $data = new DeviceManagement;
                                        $data->user_id = $request->user_id;
                                        $data->device_id = $request->device_id;
                                        $data->language_code = $request->lang_code;
                                        $data->platform = $request->platform;
                                        if($data->save()){
                                            $message = "Language has been changed 4.";
                                        }else{
                                            $message = "Something Went Wrong 3.";
                                        }
                                    }

                                }

                            }
                        // }
                        // if($data){
                        //     $message = "Language has been changed.";    
                        // }else{
                        //     $message = "something went wrong.";
                        // }
                        
                    }else{
                        $message = "language code should not be empty.";
                    }
                }else{
                    $message = "Data Insufficient.";
                }


            }elseif($request->request_type == 'get'){
                $device_id = $request->device_id;
                $user_id = $request->user_id;
                $data = DeviceManagement::where(function($query) use($device_id,$user_id){
                    if(!empty($user_id)){
                        $query->where('user_id',$user_id);
                    }else{
                        $query->where('device_id',$device_id);
                    }
                })->first();
                if(!empty($data)){
                    $message = "data found.";
                }else{
                    $message = "Sorry data not found.";
                }
    
            }else{
                $message = "Please send request type.";
    
            }

        }catch(\Exception $e){
            $message = $e->getMessage();
        }
        return response()->json([
            'status' => $status,
            'data' => $data,
            'message' => $message
        ]);

    }
}
