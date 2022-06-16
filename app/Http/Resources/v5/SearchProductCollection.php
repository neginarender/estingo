<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'=>$data->id,
                    'name' => $data->name,
                    'thumbnail_image' => $data->thumbnail_img,
                    // 'base_price' => (double) homeBasePrice($data->id),
                    // 'stock_price'=> $data->stock_price,
                    // 'sorting_hub_price'=>$data->sorting_hub_price,
                    // 'peer_price'=> $data->peer_price,
                    //'base_discounted_price' => (double) homeDiscountedBasePrice($data->id),
                    // 'discount'=>$data->customer_discount,
                    // 'discount_percentage'=>(double)substr($data->discount_percentage,1,-1),
                    'rating' => (double) $data->rating,
                    'variant'=>$data->variant,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'cart_id'=>$data->cart_id,
                    'cart_qty'=>$data->cart_qty,
                    'MRP' => $data->MRP,
                    'stock_price'=>$data->MRP,
                    'base_price' => $data->selling_price,
                    'discount' => $data->customer_off,
                    'discount_percentage' => (double)substr($data->customer_discount,1,-1),
                    'discount_type' => json_decode($data->discount_type),
                    'links' => [
                        'details' => route('products.show', $data->id),
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id),
                        'top_from_seller' => route('products.topFromSeller', $data->id)
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
