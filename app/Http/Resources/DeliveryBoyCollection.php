<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\OrderStatus;

class DeliveryBoyCollection extends ResourceCollection
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
                return[
                    'id' => $data->id,
                    'order_id' => $data->order_id,
                    'sub_order_code' => $data->sub_order_code,
                    'no_of_items' => $data->no_of_items,
                    'delivery_name' => $data->delivery_name,
                    'delivery_type' => $data->delivery_type,
                    'delivery_date' => $data->delivery_date,
                    'delivery_time' => $data->delivery_time,
                    'delivery_status' => $data->delivery_status,
                    // 'order_status' => self::orderStatus($data->order_status),
                    'payable_amount' => $data->payable_amount,
                    'customer_discount' => $data->customer_discount,
                    'payment_status' => $data->payment_status,
                    'payment_mode' => $data->payment_mode,
                    'pay_by' => $data->pay_by,
                    'payment_response' => $data->payment_response,
                    'delivered_at' => $data->delivered_at,
                    'order_status_name' => $data->order_status_name,
                    'shipping_address' => json_decode($data->shipping_address),
                    'code' => $data->code
                ];
            }),
        ];    
        //return parent::toArray($request);
    }

    public static function orderStatus($order_status_id = null){
        $arr = array();
        $arr = OrderStatus::where('id','>',$order_status_id)->where('id','!=',7)->select('id','name')->get();
        return $arr;

    }
}
