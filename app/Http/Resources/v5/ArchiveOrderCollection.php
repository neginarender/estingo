<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ArchiveOrderCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return[
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'code' => $data->code,
                    'shipping_address' => json_decode($data->shipping_address),
                    'shipping_pin_code' => $data->shipping_pin_code,
                    'payment_type' => $data->payment_type,
                    'payment_status' => $data->payment_status,
                    'order_status' => $data->order_status,
                    'amount' => (double)$data->grand_total
                ];
            })

        ];
        //return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
