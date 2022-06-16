<?php

namespace App\Http\Resources\v4;

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
                    'name' => trans($data->name),
                    'category_id' => $data->category_id,
                    'subcategory_id' => $data->subcategory_id,
                    'subsubcategory_id' => $data->subsubcategory_id,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    // 'quantity'=>$data->quantity,
                    'variant'=>$data->variant,
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
                        'related'=> env('APP_URL')."api/v4/products/related/".$data->product_id,
                        'product_link'=> env('APP_URL')."product/".$data->slug
                    ],
                    'subscribed_by_user' => $this->checkALreadySubscribed($data->product_id),
                    
                    
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

    public function checkALreadySubscribed($product_id){
        if(!empty($_SERVER['HTTP_SORTINGHUBID']) && !empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
            $shortId = $_SERVER['HTTP_SORTINGHUBID'];
            $subscribed = \App\SubscribedOrder::where('user_id',$user_id)->where('sorting_id',$shortId)->where('product_id',$product_id)->where('status',1)->first();
            if(!empty($subscribed)){
                return true;
            }else{
                return false;
            }
        }
        
    }


}
