<?php

namespace App\Http\Resources\v5;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use App\PeerSetting;
use App\Product;
use App\ProductStock;
use App\MappingProduct;
use App\Distributor;
use App\Wishlist;
use DB;

class RecurringCollection extends ResourceCollection
{
    protected static $isReview = false;
    protected static $yourRating = 0;

    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'product_id' => (integer) $data->product_id,
                    'name' => $data->name,
                    'category_id' => $data->category_id,
                    'subcategory_id' => $data->subcategory_id,
                    'subsubcategory_id' => $data->subsubcategory_id,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    // 'quantity'=>$data->quantity,

                    'photos' => json_decode($data->photos),
                    'thumbnail_image' => $data->thumbnail_img,
                    'unit' => $data->unit,
                    'tax' => (double) $data->tax,
                    'description' => $data->description,
                    'MRP' => $data->MRP,
                    'stock_price'=>(double)$data->MRP,
                    'base_price' => round($data->selling_price,2),
                    'discount' => $data->customer_off,
                    'discount_percentage' => json_decode($data->customer_discount),
                    'discount_type' => json_decode($data->discount_type),
                    'links' => [
                        'reviews' => route('api.reviews.index', $data->product_id),
                        'related' => route('products.related', $data->product_id),
                        'product_link'=> env('APP_URL')."/product/".$data->slug
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
