<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrderCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'order_type' => $data->order_type,
                    'shipping_address' => json_decode($data->shipping_address),
                    'shipping_pin_code' => $data->shipping_pin_code,
                    'sorting_hub_id' => $data->sorting_hub_id,
                    'payment_type' => $data->payment_type,
                    'payment_status' => $data->payment_status,
                    'order_status' => $data->order_status,
                    'amount' => (double)($data->grand_total + $data->wallet_amount),
                    'peerCommission' => isset($data->referal_commision_discount)?$data->referal_commision_discount:0
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
}
