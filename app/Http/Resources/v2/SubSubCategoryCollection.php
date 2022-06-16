<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SubSubCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'name' => $data->name,
                    'links' => [
                        'products' => route('apiv2.products.subSubCategory', $data->id)
                    ]
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
