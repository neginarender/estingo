<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AddressCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'      => $data->id,
                    'name'    => $data->name,
                    'user_id' => $data->user_id,
                    'address' => $data->address,
                    'country' => $data->country,
                    'city' => $data->city,
                    'state'=>$data->state,
                    'postal_code' => $data->postal_code,
                    'phone' => $data->phone,
                    'block'=>@\App\Block::find($data->block_id)->name,
                    'panchayat'=>$data->village,
                    'set_default' => $data->set_default,
                    'tag' => ucfirst($data->tag)
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
