<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class HomeCategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->category->id,
                    'name' => $data->category->name,
                    'banner' => $data->category->banner,
                    'icon' => $data->category->icon,
                    'links' => [
                        'products' => route('apiv2.products.category', $data->category->id),
                        'sub_categories' => route('apiv2.subCategories.index', $data->category->id)
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
