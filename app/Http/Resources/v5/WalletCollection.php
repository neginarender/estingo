<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class WalletCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'=> $data->id,
                    'amount' => $data->amount,
                    'payment_method' => $data->payment_method,
                    'tr_type'=> $data->tr_type,
                    'created_at'=> date('d-m-Y H:i:s',strtotime($data->created_at)),
                    'order_id' => $data->order_id,
                    'subscribed_id' => $data->subscribed_id,
                    'Order_type' => empty($data->order_id)?"Recurring":"Normal"
                    // 'approval' => $data->offline_payment ? ($data->approval == 1 ? "Approved" : "Decliend") : "N/A"
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
