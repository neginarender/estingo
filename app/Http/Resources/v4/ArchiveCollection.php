<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use DB;

class ArchiveCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'payment_status' => $data->payment_status,
                    'payment_type' => $data->payment_type,
                    'order_status' => $data->order_status,
                    'shipping_address' => json_decode($data->shipping_address),
                    'date' => date('d-m-Y H:i:s',strtotime($data->date)),
                    'grand_total' => $data->grand_total+$data->wallet_amount,
                    //'deliveryType' => $this->getDeliveryType($data->id),
                    //'is_fresh' => $this->isFresh($data->id),
                    //'is_grocery' => $this->isGrocery($data->id),
                    //'delivery_detail' => $this->getDeliveryDetail($data->id),
                    
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

    public function getDeliveryType($id){
        $deliveryType = DB::table('sub_orders')
            ->where('order_id','=',$id)->where('status',1)->pluck('delivery_type')
            ->first();
        return $deliveryType;
    }

    public function isFresh($id){
        $is_fresh = 0;
        $schedule = DB::table('sub_orders')
                ->where('order_id','=',$id)->where('status',1)
                ->get();
        foreach($schedule as $data){
                if($data->delivery_name == 'fresh'){
                    $is_fresh = 1;
                }
            }

        return $is_fresh;
    }

    public function isGrocery($id){
        $is_grocery = 0;
        $schedule = DB::table('sub_orders')
                ->where('order_id','=',$id)->where('status',1)
                ->get();
        foreach($schedule as $data){
                if($data->delivery_name == 'grocery'){
                    $is_grocery = 1;
                }
            }

        return $is_grocery;
    }

    public function getDeliveryDetail($id){
        $deliveryType = $this->getDeliveryType($id);
        $schedule = DB::table('sub_orders')
                ->where('order_id','=',$id)->where('status',1)
                ->get();

        if($deliveryType != ''){
                $slot = array();
                foreach($schedule as $value){
                    $slot[$value->delivery_name]['sub_order_code'] = $value->sub_order_code;
                    $slot[$value->delivery_name]['delivery_date'] = $value->delivery_date;
                    $slot[$value->delivery_name]['delivery_time'] = $value->delivery_time;
                }

            }else{
                $slot = array();
                $slot['fresh']['delivery_date'] = '';
                $slot['fresh']['delivery_time'] = '';
                $slot['grocery']['delivery_date'] = '';
                $slot['grocery']['delivery_time'] = '';
            }
        return $slot;
    }

}
