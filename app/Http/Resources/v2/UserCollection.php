<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => (integer) $data->id,
                    'name' => $data->name,
                    'type' => $data->user_type,
                    'email' => $data->email,
                    'avatar' => $data->avatar,
                    'avatar_original' => $data->avatar_original,
                    'address' => empty($this->getAddress($data->id))?'':$this->getAddress($data->id)->address,
                    'city' => empty($this->getAddress($data->id))?'':$this->getAddress($data->id)->city,
                    'country' => empty($this->getAddress($data->id))?'':$this->getAddress($data->id)->country,
                    'postal_code' => empty($this->getAddress($data->id))?'':$this->getAddress($data->id)->postal_code,
                    'phone' => $data->phone,
                    'peer_partner'=>$data->peer_partner,
                    'peer_type'=>$this->peer_partner($data->id,'peer_type'),
                    'peer_code'=>$this->peer_partner($data->id,'code'),
                    'user_cart'=>$this->user_cart()
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }

    public function peer_partner($id,$type){
        $peer_partner = \App\PeerPartner::where('user_id',$id)->first();
        if(!is_null($peer_partner)){
            return $peer_partner->$type;
        }
        $type = "";
        return $type;

    }

    public function user_cart(){
        $user_cart = false;
        if(isset($_SERVER['HTTP_DEVICE']) && !empty($_SERVER['HTTP_DEVICE'])){
            $check_user_cart = \App\Models\Cart::where('device_id',$_SERVER['HTTP_DEVICE'])->count();
            if($check_user_cart){
                $user_cart = true;
            }
        }
        return $user_cart;
    }

    public function getAddress($user_id){
        $defaultAddress = \App\Address::where('user_id',$user_id)->where('set_default','1')->first();
        if(is_null($defaultAddress)){
            $address = \App\Address::where('user_id',$user_id)->first();
        }else{
            $address = $defaultAddress;
        }
        
        if(!is_null($address)){
            return $address;
        }
        $address = "";
        return $address;
    }
}
