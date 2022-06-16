<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SubCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'=>$data->id,
                    'name' => trans($data->name),
                    'subSubCategories' => new SubSubCategoryCollection($data->subSubCategories),
                    'links' => [
                        'products' => route('apiv5.products.subCategory', $data->id)
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
