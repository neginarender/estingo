<?php

namespace App\Http\Resources\v2;

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

class ProductDetailCollection extends ResourceCollection
{
    protected static $isReview = false;
    protected static $yourRating = 0;

    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => (integer) $data->id,
                    'name' => $data->name,
                    'max_purchase_qty'=>$data->max_purchase_qty,
                    'quantity'=>$data->quantity,
                    'added_by' => $data->added_by,
                    'user' => [
                        'name' => is_null($data->user)?NULL:$data->user['name'],
                        'email' => is_null($data->user)?NULL:$data->user['email'],
                        'avatar' => is_null($data->user)?NULL:$data->user['avatar'],
                        'avatar_original' => is_null($data->user)?NULL:$data->user['avatar_original'],
                        'shop_name' => $data->added_by == 'admin' ? '' : $data->user->shop->name,
                        'shop_logo' => $data->added_by == 'admin' ? '' : $data->user->shop->logo,
                        'shop_link' => $data->added_by == 'admin' ? '' : route('shops.info', $data->user->shop->id)
                    ],
                    'category' => [
                        'name' => $data->category->name,
                        'banner' => $data->category->banner,
                        'icon' => $data->category->icon,
                        'links' => [
                            'products' => route('api.products.category', $data->category_id),
                            'sub_categories' => route('subCategories.index', $data->category_id)
                        ]
                    ],
                    'sub_category' => [
                        'name' => $data->subCategory != null ? $data->subCategory->name : null,
                        'links' => [
                            'products' => $data->subCategory != null ? route('products.subCategory', $data->subcategory_id) : null
                        ]
                    ],
                    'brand' => [
                        'name' => $data->brand != null ? $data->brand->name : null,
                        'logo' => $data->brand != null ? $data->brand->logo : null,
                        'links' => [
                            'products' => $data->brand != null ? route('api.products.brand', $data->brand_id) : null
                        ]
                    ],
                    'photos' => json_decode($data->photos),
                    'thumbnail_image' => $data->thumbnail_img,
                    'tags' => explode(',', $data->tags),
                    'price_lower' => (double) explode('-', homeDiscountedPrice($data->id))[0],
                    'price_higher' => (double) explode('-', homeDiscountedPrice($data->id))[1],
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => json_decode($data->colors),
                    'todays_deal' => (integer) $data->todays_deal,
                    'featured' => (integer) $data->featured,
                    'current_stock' => (integer) $data->current_stock,
                    'unit' => $data->unit,
                    'tax' => (double) $data->tax,
                    'tax_type' => $data->tax_type,
                    'shipping_type' => $data->shipping_type,
                    'shipping_cost' => (double) $data->shipping_cost,
                    'number_of_sales' => (integer) $data->num_of_sale,
                    'description' => $data->description,
                    'is_review'=>self::$isReview,
                    'reviewCount'=>(integer) Review::where(['product_id' => $data->id])->count(),
                    'yourRating' => self::$yourRating,
                    'variant'=>$data->variant,
                    'MRP' => $data->MRP,
                    'stock_price'=>(double)$data->MRP,
                    'base_price' => round($data->selling_price,2),
                    'discount' => $data->customer_off,
                    'discount_percentage' => json_decode($data->customer_discount),
                    'discount_type' => json_decode($data->discount_type),
                    'rating' => round($data->rating_avg,1),
                    'sales' => (integer) $data->num_of_sale,
                    'cart_qty'=>$data->cart_qty,
                    'cart_id'=>$data->cart_id,
                    'in_wishlist'=>$data->in_wishlist,
                    'wishlist_id'=>isset($data->wishlist_id)?$data->wishlist_id:0,
                    'links' => [
                        'reviews' => route('apiv2.reviews.index', $data->id),
                        'related' => route('apiv2.products.related', $data->id),
                        'product_link'=> env('APP_URL')."product/".$data->slug
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

    protected function convertToChoiceOptions($data){
        $result = array();
        foreach ($data as $key => $choice) {
            $item['name'] = $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->name;
            $item['options'] = $choice->values;
            array_push($result, $item);
        }
        return $result;
    }
    
    public static function isReviewAble($product_id){
        $user = null;
        if(isset($_SERVER['HTTP_USERID']) && !empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
            $user = \App\User::find($user_id);
        }
        
        $product = \App\Product::find($product_id);
        $Review = false;
        if(!is_null($user) && !is_null($product)){
            foreach($product->orderDetails as $key => $orderDetail)
            {
                if($orderDetail->order != null && $orderDetail->order->user_id == $user->id  && \App\Review::where('user_id', $user->id)->where('product_id', $product->id)->first() == null)
                {
                    $Review = true;
                }  
            } 
                
        }
        self::$isReview = $Review;
        return $Review;
    }

    public static function yourReview($id){
        if(!empty($_SERVER['HTTP_USERID'])){
            $user_id = $_SERVER['HTTP_USERID'];
            $review = Review::where('product_id',$id)->where('user_id',$user_id)->pluck('rating')->first();  
            if(!is_null($review)){
                $yourRating = $review;
            }else{
                $yourRating = 0;
            }       
        }else{
            $yourRating = 0;
        }
        self::$yourRating = $yourRating;
        return $yourRating;
    }

}
