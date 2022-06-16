<?php

namespace App\Http\Resources\v2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use DB;

class WishlistCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => (integer) $data->id,
                    'product' => [
                        'product_id' => $data->product_id,
                        'name' => $data->name,
                        'thumbnail_image' => $data->thumbnail_img,
                        'variant' => $data->variant,
                        'MRP'=> (double)$data->MRP,
                        'stock_price' => (double)$data->stock_price,
                        'base_price' => round($data->base_price,2),
                        'discount' => (double)$data->discount,
                        'discount_percentage' => json_decode($data->discount_percentage),
                        'discount_type' => json_decode($data->discount_type),
                        // 'discount' => 0,
                        // 'discount_percentage' => NULL,
                        // 'discount_type' => NULL,
                        'rating' => is_null($data->rating)?0:round($data->rating,2),
                        'unit' => $data->unit,
                        'links' => [
                            'details' => env('APP_URL')."api/v2/products/".$data->id,
                            'reviews' => route('apiv2.reviews.index', $data->id),
                            'related' => route('apiv2.products.related', $data->id),
                            'top_from_seller' => route('products.topFromSeller', $data->id)
                        ],
                        'available' => $data->is_available,
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
