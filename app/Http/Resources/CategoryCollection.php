<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'name' => $data->name,
                    'banner' => $data->banner,
                    'icon' => $data->icon,
                    // 'brands' => brandsOfCategory($data->id),
                    'links' => [
                        'products' => route('apiv1.products.category', $data->id),
                        'sub_categories' => route('apiv1.subCategories.index', $data->id)
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
