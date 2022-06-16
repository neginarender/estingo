<?php

namespace App\Http\Resources\v3;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\PeerSetting;
use App\Product;
use App\ProductStock;
use App\MappingProduct;

class HomepageCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'product_id'=>$data->id,
                    'category_id'=>$data->category_id,
                    'subcategory_id'=>$data->subcategory_id,
                    'name' => $data->name,
                    'photos' => json_decode($data->photos),
                    'thumbnail_image' => $data->thumbnail_img,
                    'stock_price' => (double)$data->MRP,
                    'base_price' => round($data->selling_price,2),
                    // 'base_price' => (double) $data->selling_price,
                    'discount' => $data->customer_off,
                    'discount_percentage' => json_decode($data->customer_discount),
                    'discount_type' => json_decode($data->discount_type),
                    'quantity'=>is_null($data->quantity)?0:$data->quantity,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'variant'=>$data->variant,
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' =>(integer) $data->featured,
                    'choice_options' => $data->choice_options,
                    'unit' => $data->unit,
                    'rating' => round($data->rating_avg,1),
                    'sales' => (integer) $data->num_of_sale,
                    'cart_qty'=>$data->cart_qty,
                    'cart_id'=>$data->cart_id,
                    'in_wishlist'=>$data->in_wishlist,
                    'wishlist_id'=>isset($data->wishlist_id)?$data->wishlist_id:0,
                    'links' => [
                        'details' => env('APP_URL')."api/v3/products/".$data->id,
                        // 'reviews' => route('api.reviews.index', $data->id),
                        'reviews' => env('APP_URL')."api/v3/reviews/product/".$data->id,
                        // 'related' => route('products.related', $data->id),
                        'related' => env('APP_URL')."api/v3/products/related/".$data->id,
                        // 'top_from_seller' => route('products.topFromSeller', $data->id)
                        'top_from_seller' => env('APP_URL')."api/v3/products/top-from-seller/".$data->id
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
