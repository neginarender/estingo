<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;
use DB;

class OrderHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data){
                $schedule = DB::table('sub_orders')
                ->where('order_id','=',$data->id)->where('status',1)
                ->get();

                $is_fresh = 0;
                $is_grocery = 0;
                foreach($schedule as $data){
                    if($data->delivery_name == 'fresh'){
                        $is_fresh = 1;
                    }
                    if($data->delivery_name == 'grocery'){
                        $is_grocery = 1;
                    }
                }

                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'order_type' => $data->order_type,
                    'grand_total' => ($data->grand_total+$data->wallet_amount),
                    'shipping_address' => json_decode($data->shipping_address),
                    'payment_status' => $data->payment_status,
                    'discount' => $data->discount,
                    'payment_type' => $data->payment_type,
                    'date' => date('d-m-Y H:i:s',$data->date),
                    'order_status'=> $data->order_status,
                    'wallet_amount' => $data->wallet_amount,
                    'is_fresh' => $is_fresh,
                    'is_grocery' => $is_grocery

                ];

            })
        ];

        // return parent::toArray($request);
    }


    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
