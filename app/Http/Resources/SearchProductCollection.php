<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class SearchProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'=>$data->id,
                    'name' => trans($data->name),
                    'thumbnail_image' => $data->thumbnail_img,
                    'base_price' => (double) homeBasePrice($data->id),
                    'stock_price'=> $data->stock_price,
                    'sorting_hub_price'=>$data->sorting_hub_price,
                    'peer_price'=> $data->peer_price,
                    //'base_discounted_price' => (double) homeDiscountedBasePrice($data->id),
                    'discount'=>$data->customer_discount,
                    'discount_percentage'=>(double)substr($data->discount_percentage,1,-1),
                    'rating' => (double) $data->rating,
                    'variant'=>$data->variant,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'cart_id'=>$data->cart_id,
                    'cart_qty'=>$data->cart_qty,
                    'links' => [
                        'details' => env('APP_URL')."api/v1/products/".$data->id,
                        'reviews' => route('apiv1.reviews.index', $data->id),
                        'related' => route('apiv1.products.related', $data->id),
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
