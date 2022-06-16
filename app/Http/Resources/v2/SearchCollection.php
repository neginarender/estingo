<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Product;
use App\MappingProduct;
use App\Category;

class SearchCollection extends ResourceCollection
{
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'product_id'=>$data->id,
                    'name' => $data->name,
                    'photos' => $data->thumbnail_img,
                    'category_id' =>$data->category_id,
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
