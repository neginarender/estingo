<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\State;
use App\City;
use App\Area;
use App\Block;

class BlockCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'block_id'=>(integer)$data->id,
                    'name' => $data->name,
                    'district_id' =>(integer)$data->district_id,
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
