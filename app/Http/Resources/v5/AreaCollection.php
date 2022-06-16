<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\State;
use App\City;
use App\Area;

class AreaCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'area_id'=>(integer)$data->area_id,
                    'area_name' => $data->area_name,
                    'post_office' => $data->post_office,
                    'district_id' =>(integer)$data->district_id,
                    'pincode' =>(integer)$data->pincode,
                    'zone' =>(integer)$data->zone,
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
