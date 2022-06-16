<?php

namespace App\Http\Resources\v4;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\State;
use App\MappingProduct;
use App\Category;

class StateCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'state_id'=>(integer)$data->id,
                    'name' => $data->name,
                    'country_id' => (integer)$data->country_id,
                    'region_id' =>(integer)$data->region_id,
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
