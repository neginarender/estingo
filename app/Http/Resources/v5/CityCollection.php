<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\State;
use App\City;

class CityCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'city_id'=>(integer)$data->id,
                    'name' => $data->name,
                    'state_id' => (integer)$data->state_id,
                    'mapped' =>(integer)$data->mapped,
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
